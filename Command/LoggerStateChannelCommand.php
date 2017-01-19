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
                    $this->loadChannelInfomation($key, $lock, $channel);
                }

                $dateWithMaxTime = $lock->getLockedAt();
                $dateWithMaxTime->add(new DateInterval('PT' . $this->timeLimit . 'S'));
                if (new \DateTime() > ($dateWithMaxTime)) {
                    $this->notifier->process($this->channelName);
                }
            }
        }
    }

    /**
     * @param string $key
     * @param Lock   $lock
     * @param array  $channel
     */
    private function loadChannelInfomation($key, Lock $lock, $channel)
    {
        $this->channelName = $key;
        $this->timeLimit = $channel['duration_limit_per_run'] + $this->extraTime;

        if (preg_match(sprintf('/^%s/', $key), $lock->getChannel()) && true === $channel['dynamic']) {
            $this->channelName = $lock->getChannel();
            $this->timeLimit = $channel['duration_limit_per_run'] + $this->extraTime;
        }
    }
}
