<?php
/**
 * Created by mcfedr on 03/06/2014 21:50
 */

namespace Mcfedr\ResqueQueueDriverBundle\Manager;

use Mcfedr\QueueManagerBundle\Exception\NoSuchJobException;
use Mcfedr\QueueManagerBundle\Exception\WrongJobException;
use Mcfedr\QueueManagerBundle\Manager\QueueManager;
use Mcfedr\QueueManagerBundle\Queue\Job;
use Mcfedr\ResqueQueueDriverBundle\Queue\ResqueJob;

class ResqueQueueManager implements QueueManager
{
    /**
     * @var array
     */
    private $kernelOptions;

    /**
     * @var string
     */
    protected $defaultQueue;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var boolean
     */
    private $trackStatus;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        $this->defaultQueue = $options['default_queue'];
        $this->setKernelOptions($options['kernel_options']);
        $this->debug = $options['debug'];
        if (!$this->debug) {
            \Resque::setBackend("{$options['host']}:{$options['port']}");
            if (isset($options['prefix'])) {
                \Resque_Redis::prefix($options['prefix']);
            }
        }
        $this->trackStatus = $options['track_status'];
    }

    /**
     * @return array
     */
    private function getKernelOptions()
    {
        return $this->kernelOptions;
    }

    /**
     * @param array $kernelOptions
     */
    private function setKernelOptions($kernelOptions)
    {
        $this->kernelOptions = $kernelOptions;

        //Convert root_dir to be relative to the resque bundle paths, this makes it possible to deploy workers in different places
        if (array_key_exists('kernel.root_dir', $this->kernelOptions)) {
            $this->kernelOptions['kernel.root_dir'] = $this->getRelativePath(__DIR__, $this->kernelOptions['kernel.root_dir']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($name, array $arguments = [], array $options = [])
    {
        $queue = isset($options['queue']) ? $options['queue'] : $this->defaultQueue;

        $resqueArguments = [
            'name' => $name,
            'arguments' => $arguments,
            'kernel_options' => $this->kernelOptions
        ];

        $trackJobStatus = isset($options['track_status']) ? $options['track_status'] : $this->trackStatus;

        $id = null;

        $when = isset($options['when']) ? $options['when'] : null;

        if (!$this->debug) {
            if ($when) {
                \ResqueScheduler::enqueueAt($when, $queue, \Mcfedr\ResqueQueueDriverBundle\Resque\Job::class, $resqueArguments, $trackJobStatus);
            } else {
                $id = \Resque::enqueue($queue, \Mcfedr\ResqueQueueDriverBundle\Resque\Job::class, $resqueArguments, $trackJobStatus);
            }
        }

        return new ResqueJob($resqueArguments, $options, $id, $when, $queue, \Mcfedr\ResqueQueueDriverBundle\Resque\Job::class, $trackJobStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Job $job)
    {
        if (!$job instanceof ResqueJob) {
            throw new WrongJobException('Resque queue manager can only delete resque jobs');
        }

        if (!$job->isFutureJob()) {
            throw new WrongJobException('Resque queue manager can only delete future jobs');
        }

        if ($this->debug) {
            return;
        }

        if (\ResqueScheduler::removeDelayedJobFromTimestamp($job->getWhen(), $job->getQueue(), $job->getClass(), $job->getResqueArguments(), $job->isTrackStatus()) < 1) {
            throw new NoSuchJobException('No jobs were found');
        }
    }

    private function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach($from as $depth => $dir) {
            // find first non-matching dir
            if($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
