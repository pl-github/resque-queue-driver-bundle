<?php
/**
 * Created by mcfedr on 03/06/2014 22:00
 */

namespace Mcfedr\ResqueQueueDriverBundle\Resque;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Job
 * @package Mcfedr\ResqueQueueDriverBundle\Resque
 *
 * This is the job that Resque will run, commands are then run within the symfony container
 */
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
        $this->getContainer()->get($this->args['name'])->execute($this->args['arguments']);
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
     * This is largely copied from how the test client finds and setups a kernel
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
