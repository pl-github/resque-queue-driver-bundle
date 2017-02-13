<?php

namespace Mcfedr\ResqueQueueDriverBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class McfedrResqueQueueDriverExtensionTest extends WebTestCase
{
    public function testConfiguration()
    {
        $client = static::createClient();
        $this->assertTrue($client->getContainer()->has('mcfedr_queue_manager.default'));
    }
}
