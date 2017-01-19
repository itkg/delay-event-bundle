<?php

namespace Itkg\DelayEventBundle\Document;

use Itkg\DelayEventBundle\Model\Lock as BaseLock;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Lock
 *
 * @ODM\Document(
 *   repositoryClass="Itkg\DelayEventBundle\Repository\LockRepository",
 *   collection="itkg_delay_event_lock"
 * )
 */
class Lock extends BaseLock
{
    /**
     * @var string
     *
     * @ODM\id
     */
    protected $id;

    /**
     * @var bool
     *
     * @ODM\Boolean
     */
    protected $commandLocked = false;

    /**
     * @var string
     *
     * @ODM\String
     */
    protected $channel = '';

    /**
     * @var \DateTime
     *
     * @ODM\Date
     */
    protected $lockedAt;

    /** @var string
     *
     * @ODM\String
     */
    protected $lockedBy = '';
}
