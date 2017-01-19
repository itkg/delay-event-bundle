<?php

namespace Itkg\DelayEventBundle\Notifier;

/**
 * Interface NotifierInterface
 */
interface NotifierInterface
{
    /**
     * @param string $channelName
     *
     * @return mixed
     */
    public function process($channelName);
}
