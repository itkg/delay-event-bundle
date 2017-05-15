<?php

namespace Itkg\DelayEventBundle\Document;

use Gedmo\Timestampable\Traits\TimestampableDocument;
use Itkg\DelayEventBundle\Model\Event as BaseEvent;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Event
 *
 * @ODM\Document(
 *   repositoryClass="Itkg\DelayEventBundle\Repository\EventRepository",
 *   collection="itkg_delay_event"
 * )
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 *
 * @ODM\Indexes({
 *   @ODM\Index(keys={"failed"="asc", "originalName"="asc", "groupFieldIdentifier"="asc", "createdAt":"asc"}, name="failed_name_group_date")
 * })
 */
class Event extends BaseEvent
{
    use TimestampableDocument;

    /**
     * @var string
     *
     * @ODM\id
     */
    protected $id;

    /**
     * @var string
     *
     * @ODM\String
     */
    protected $originalName;

    /**
     * @var bool
     *
     * @ODM\Boolean
     */
    protected $async = true;

    /**
     * @var bool
     *
     * @ODM\Boolean
     */
    protected $failed = false;

    /**
     * @var int
     *
     * @ODM\Int
     */
    protected $tryCount = 0;

    /**
     * @var string
     *
     * @ODM\String
     */
    protected $groupFieldIdentifier = self::DEFAULT_GROUP_IDENTIFIER;
}
