<?php

namespace Itkg\DelayEventBundle\Command;

use DateInterval;
use Itkg\DelayEventBundle\Document\Lock;
use Itkg\DelayEventBundle\Handler\LockHandlerInterface;
use Itkg\DelayEventBundle\Notifier\NotifierInterface;
use Itkg\DelayEventBundle\Repository\EventRepository;
use Itkg\DelayEventBundle\Repository\LockRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;

/**
 * Class LoggerStateChannelCommand
 */
class LoggerStateChannelCommand extends ContainerAwareCommand
{

    /**
     * @var array
     */
    private $channels;

    /**
     * @var LockRepository
     */
    private $lockRepository;

    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var integer
     */
    private $timeLimit;

    /**
     * @var string
     */
    private $channelName;

    /**
     * @var integer
     */
    private $extraTime;


    /**
     * @param LockRepository    $lockRepository
     * @param NotifierInterface $notifier
     * @param array             $channels
     * @param null              $name
     */
    public function __construct(
        LockRepository $lockRepository,
        NotifierInterface $notifier,
        array $channels = [],
        $name = null
    ) {
        $this->lockRepository = $lockRepository;
        $this->notifier = $notifier;
        $this->channels = $channels;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('itkg_delay_event:logger')
            ->setDescription('Log state channel')
            ->addArgument(
                'time',
                InputArgument::REQUIRED,
                'extra time for detect lock'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->extraTime = $input->getArgument('time');

        if (!isset($this->extraTime)) {
            $output->writeln(
                sprintf(
                    '<info>Argument time %s is required.</info>',
                    $this->extraTime
                )
            );
        }

        $locks = $this->lockRepository->findAll();

        /** @var Lock $lock */
        foreach ($locks as $lock) {
            if ($lock->isCommandLocked()) {
                foreach ($this->channels as $key => $channel) {
                    if ($this->isLockedChannelInfomation($key, $lock, $channel, $output)) {

                        $dateWithMaxTime = $lock->getLockedAt();

                        $time = $channel['duration_limit_per_run'] + $this->extraTime;
                        $dateWithMaxTime->add(
                            new DateInterval('PT' . $time . 'S')
                        );
                        if (new \DateTime() > ($dateWithMaxTime)) {
                            $this->notifier->process($key);
                        }

                        break;
                    }
                }


            }
        }
    }

    /**
     * @param      $key
     * @param Lock $lock
     * @param      $channel
     * @return bool
     */
    private function isLockedChannelInfomation($key, Lock $lock, $channel)
    {

        if (preg_match(sprintf('/^%s/', $key), $lock->getChannel()) && true === $channel['dynamic'] || $key == $lock->getChannel() && false === $channel['dynamic']) {

            return true;
        }

        return false;
    }
}
