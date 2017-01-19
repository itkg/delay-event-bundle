<?php

namespace Itkg\DelayEventBundle\Model;

use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Class Event
 */
class Event extends BaseEvent
{
    const DEFAULT_GROUP_IDENTIFIER = 'default_group_identifier';
    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var bool
     */
    protected $delayed = true;

    /**
     * @var bool
     */
    protected $failed = false;

    /**
     * @var int
     */
    protected $tryCount = 0;

    /**
     * @var string
     */
    protected $groupFieldIdentifier;

    /**
     * @param bool $delayed
     */
    public function __construct($delayed = true)
    {
        $this->delayed = $delayed;
    }
    /**
     * @return boolean
     */
    public function isDelayed()
    {
        return $this->delayed;
    }

    /**
     * @param boolean $delayed
     *
     * @return $this
     */
    public function setDelayed($delayed)
    {
        $this->delayed = $delayed;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     *
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return $this->failed;
    }

    /**
     * @param bool $failed
     *
     * @return $this
     */
    public function setFailed($failed)
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * @return int
     */
    public function getTryCount()
    {
        return $this->tryCount;
    }

    /**
     * @param int $tryCount
     *
     * @return $this
     */
    public function setTryCount($tryCount)
    {
        $this->tryCount = $tryCount;

        return $this;
    }

    /**
     * Increase try counter
     */
    public function increaseTryCount()
    {
        $this->tryCount ++;
    }

    /**
     * @return string
     */
    public function getGroupFieldIdentifier()
    {
        return $this->groupFieldIdentifier;
    }
}
