<?php

namespace Itkg\DelayEventBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Itkg\DelayEventBundle\Model\Event;

/**
 * Class EventRepository
 */
class EventRepository extends DocumentRepository
{
    /**
     * @param bool        $failed
     * @param array       $eventTypeIncluded
     * @param array       $eventTypeExcluded
     * @param string|null $groupFieldIdentifier
     * @return Event
     */
    public function findFirstTodoEvent($failed = false, array $eventTypeIncluded = [], array $eventTypeExcluded = [], $groupFieldIdentifier = null)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('failed')->equals($failed);

        if (!empty($eventTypeIncluded)) {
            $qb->field('originalName')->in($eventTypeIncluded);
        }

        if (!empty($eventTypeExcluded)) {
            $qb->field('originalName')->notIn($eventTypeExcluded);
        }

        if (null !== $groupFieldIdentifier) {
            $qb->field('groupFieldIdentifier')->equals($groupFieldIdentifier);
        }

        $qb->sort('createdAt', 'asc');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param array $includedEvents
     * @param array $excludedEvents
     *
     * @return array
     */
    public function findDistinctFieldGroupIdentifierByChannel(array $includedEvents, array $excludedEvents)
    {
        $qb = $this->createQueryBuilder();
        $qb->distinct('groupFieldIdentifier');
        $qb->field('failed')->equals(false);

        if (!empty($includedEvents)) {
            $qb->field('originalName')->in($includedEvents);
        }

        if (!empty($excludedEvents)) {
            $qb->field('originalName')->notIn($excludedEvents);
        }

        return $qb->getQuery()->execute();
    }
}
