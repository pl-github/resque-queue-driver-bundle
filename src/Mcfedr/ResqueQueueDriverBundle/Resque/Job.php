<?php

namespace Mcfedr\ResqueQueueDriverBundle\Resque;

use Mcfedr\QueueManagerBundle\Exception\UnexpectedJobDataException;
use Mcfedr\ResqueQueueDriverBundle\Queue\ResqueJob;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

class Job
{
    /**
     * @var array This is where Resque injects the arguments
     */
    public $args;

    /**
     * @var string This is where Resque injects the current queue name
     */
    public $queue;

    /**
     * @var \Resque_Job This is where Resque injects its Job
     */
    public $job;

    public function perform()
    {
        if (!is_array($this->args) || !isset($this->args['name']) || !isset($this->args['arguments']) || !isset($this->args['kernel_options'])) {
            throw new UnexpectedJobDataException('Resque message missing data fields name, arguments and kernel_options');
        }

        $this->getContainer()->get('mcfedr_queue_manager.job_executor')->executeJob(new ResqueJob($this->args, null, null, $this->queue, static::class, null));
    }

    /**
     * @var KernelInterface
     */
    private $kernel = null;

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        if ($this->kernel === null) {
            $this->kernel = $this->createKernel();
            $this->kernel->boot();
        }

        return $this->kernel->getContainer();
    }

    /**
     * This is largely copied from how the test client finds and setups a kernel.
     *
     * @return KernelInterface
     */
    private function createKernel()
    {
        $iterator = (new Finder())
            ->name('*Kernel.php')
            ->depth(0)
            ->in(__DIR__ . '/' . $this->args['kernel_options']['kernel.root_dir'])
            ->getIterator();
        $iterator->rewind(); //Seems weird that I have rewind a new iterator, but I do
        /** @var SplFileInfo $file */
        $file = $iterator->current();
        $class = $file->getBasename('.php');

        require_once $file;

        return new $class(
            isset($this->args['kernel_options']['kernel.environment']) ? $this->args['kernel_options']['kernel.environment'] : 'dev',
            isset($this->args['kernel_options']['kernel.debug']) ? $this->args['kernel_options']['kernel.debug'] : true
        );
    }

    public function tearDown()
    {
        if ($this->kernel) {
            $this->kernel->shutdown();
        }
    }
}
