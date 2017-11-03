<?php

namespace Mcfedr\ResqueQueueDriverBundle\Tests\Resque\Job;

use Mcfedr\ResqueQueueDriverBundle\Resque\Job;
use Mcfedr\ResqueQueueDriverBundle\Worker\TestWorker;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testPerform()
    {
        $job = new Job();
        $job->args = [
            'name' => TestWorker::class,
            'arguments' => ['first' => 1, 'second' => 'second'],
            'kernel_options' => [
                'kernel.root_dir' => '../../../../tests/',
                'kernel.environment' => 'test',
                'kernel.debug' => true,
            ],
        ];
        $job->perform();
    }
}
