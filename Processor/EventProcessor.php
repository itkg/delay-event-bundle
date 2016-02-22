<?php

namespace Itkg\DelayEventBundle\Processor;

use Itkg\DelayEventBundle\Event\FailProcessedEvent;
use Itkg\DelayEventBundle\Event\ProcessedEvents;
use Itkg\DelayEventBundle\Event\SuccessProcessedEvent;
use Itkg\DelayEventBundle\Model\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventProcessor
 */
class EventProcessor implements EventProcessorInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $config = array();

    /**
     * @var array
     */
    private $eventsConfig = array();

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param array                    $config
     * @param array                    $eventsConfig
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, array $config, array $eventsConfig)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
        $this->eventsConfig = $eventsConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Event $event)
    {
        $exception = null;
        try {
            $this->eventDispatcher->dispatch($event->getOriginalName(), $event);
            $this->eventDispatcher->dispatch(ProcessedEvents::SUCCESS, new SuccessProcessedEvent($event));
        } catch (\Exception $e) {
            $maxRetryCount = $this->config['retry_count'][$this->eventsConfig[$event->getOriginalName()]['type']];
            $this->eventDispatcher->dispatch(ProcessedEvents::FAIL, new FailProcessedEvent($event, $maxRetryCount));
            $exception = $e;
        }
        $this->eventDispatcher->dispatch(ProcessedEvents::FINISH, new Event());
        if ($event->isFailed() && $exception) {
            throw $exception;
        }
    }
}
