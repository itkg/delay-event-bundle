<?php

namespace Itkg\DelayEventBundle\Event;

/**
 * class ProcessedEvents 
 */
final class ProcessedEvents
{
    const SUCCESS = 'processed.event.success';

    const FAIL = 'processed.event.fail';

    const FINISH  = 'processed.event.finish';
}
