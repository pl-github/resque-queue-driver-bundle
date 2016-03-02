# Resque Queue Driver Bundle

A driver for [Queue Manager Bundle](https://github.com/mcfedr/queue-manager-bundle) that uses resque

[![Latest Stable Version](https://poser.pugx.org/mcfedr/resque-queue-driver-bundle/v/stable.png)](https://packagist.org/packages/mcfedr/resque-queue-driver-bundle)
[![License](https://poser.pugx.org/mcfedr/resque-queue-driver-bundle/license.png)](https://packagist.org/packages/mcfedr/resque-queue-driver-bundle)

## Install

### Composer

    composer require mcfedr/resque-queue-driver-bundle

### AppKernel

Include the bundle in your AppKernel

    public function registerBundles()
    {
        $bundles = [
            ...
            new Mcfedr\QueueManagerBundle\McfedrQueueManagerBundle(),
            new Mcfedr\ResqueQueueDriverBundle\McfedrResqueQueueDriverBundle(),

## Config

With this bundle installed you can setup your queue manager config similar to this:

    mcfedr_queue_manager:
        managers:
            default:
                driver: resque
                options:
                    host: 127.0.0.1
                    port: 11300
                    default_queue: mcfedr_queue

This will create a `QueueManager` service named `"mcfedr_queue_manager.default"`

## Options to `QueueManager::put`

* `when` - A `DateTime` object for when to perform the job, allows future scheduling
* `queue` - A `string` with the name of a queue
