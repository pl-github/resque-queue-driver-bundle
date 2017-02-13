<?php

namespace Mcfedr\ResqueQueueDriverBundle\Queue;

use Mcfedr\QueueManagerBundle\Queue\AbstractJob;

class ResqueJob extends AbstractJob
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $when;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $trackStatus;

    /**
     * @var array
     */
    private $resqueArguments;

    /**
     * ResqueJob constructor.
     *
     * @param string    $id
     * @param \DateTime $when
     * @param string    $queue
     * @param string    $class
     * @param bool      $trackStatus
     */
    public function __construct($resqueArguments, $id, \DateTime $when = null, $queue, $class, $trackStatus)
    {
        parent::__construct($resqueArguments['name'], $resqueArguments['arguments']);
        $this->id = $id;
        $this->when = $when;
        $this->queue = $queue;
        $this->class = $class;
        $this->trackStatus = $trackStatus;
        $this->resqueArguments = $resqueArguments;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isTrackStatus()
    {
        return $this->trackStatus;
    }

    /**
     * @return array
     */
    public function getResqueArguments()
    {
        return $this->resqueArguments;
    }

    public function isFutureJob()
    {
        return (bool) $this->getWhen();
    }
}
