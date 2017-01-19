<?php

namespace Itkg\DelayEventBundle\Model;

/**
 * Class Lock
 */
class Lock
{
    /**
     * @var bool
     */
    protected $commandLocked = false;

    /**
     * @var string
     */
    protected $channel = '';

    /**
     * @var \DateTime
     */
    protected $lockedAt;

    /**
     * @var string
     */
    protected $lockedBy;

    /**
     * @return boolean
     */
    public function isCommandLocked()
    {
        return $this->commandLocked;
    }

    /**
     * @param boolean $commandLocked
     *
     * @return $this
     */
    public function setCommandLocked($commandLocked)
    {
        $this->commandLocked = $commandLocked;

        if ($commandLocked) {
            $this->lockedAt = new \DateTime();
            $this->lockedBy = php_uname('n');
        } else {
            $this->lockedAt = null;
            $this->lockedBy = '';
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     *
     * @return Lock
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLockedAt()
    {
        return $this->lockedAt;
    }
    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }
}
