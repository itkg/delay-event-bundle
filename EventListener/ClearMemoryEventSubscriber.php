<?php

namespace Itkg\DelayEventBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Itkg\DelayEventBundle\Event\ProcessedEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ClearMemoryEventSubscriber
 */
class ClearMemoryEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param Event $event
     */
    public function onFinish(Event $event)
    {
        $this->objectManager->flush();
        $this->objectManager->clear();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ProcessedEvents::FINISH  => 'onFinish',
        );
    }
}
