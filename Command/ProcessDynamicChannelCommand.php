<?php

namespace Itkg\DelayEventBundle\Command;

use Itkg\DelayEventBundle\Handler\LockHandlerInterface;
use Itkg\DelayEventBundle\Model\Event;
use Itkg\DelayEventBundle\Model\Lock;
use Itkg\DelayEventBundle\Processor\EventProcessor;
use Itkg\DelayEventBundle\Repository\EventRepository;
use Itkg\DelayEventBundle\Repository\LockRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProcessDynamicChannelCommand
 */
class ProcessDynamicChannelCommand extends ContainerAwareCommand
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var EventProcessor
     */
    private $eventProcessor;

    /**
     * @var LockHandlerInterface
     */
    private $lockHandler;

    /**
     * @var array
     */
    private $channels;

    /**
     * @var LockRepository
     */
    private $lockRepository;

    /**
     * ProcessEventCommand constructor.
     *
     * @param EventRepository      $eventRepository
     * @param LockRepository       $lockRepository
     * @param array                $channels
     * @param null|string          $name
     */
    public function __construct(
        EventRepository $eventRepository,
        LockRepository $lockRepository,
        array $channels = [],
        $name = null
    ) {
        $this->eventRepository = $eventRepository;
        $this->lockRepository = $lockRepository;
        $this->channels = $channels;

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('itkg_delay_event:process_dynamic_channel')
            ->setDescription('Process dynamic channel')
            ->addArgument(
                'channel',
                InputArgument::REQUIRED,
                'Dynamic channel to process'
            )
            ->addOption(
                'concurrent-jobs-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Maximum concurrent jobs count for this channel. Default 5',
                5
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $input->getArgument('channel');

        $commandline = $this->getContainer()->get('itkg_delay_event.command');

        if (!isset($this->channels[$channel])) {
            $output->writeln(
                sprintf(
                    '<error>Channel <info>%s</info> is not configured.</error>',
                    $channel
                )
            );
            return;
        }

        if (!$this->channels[$channel]['dynamic']) {
            $output->writeln(
                sprintf(
                    '<error>Channel <info>%s</info> is not dynamic.</error>',
                    $channel
                )
            );
            return;
        }

        $fieldGroupIdentifierList = $this->eventRepository->findDistinctFieldGroupIdentifierByChannel(
            $this->channels[$channel]['include'],
            $this->channels[$channel]['exclude']
        )->toArray();

        $locks = $this->lockRepository->findAll();
        $lockNames = array();
        $currentMachineLockNames = array();
        /** @var Lock $lock */
        foreach ($locks as $lock) {
            $lockName = str_replace(sprintf('%s_', $channel), '', $lock->getChannel());
            if ($lock->isCommandLocked() && in_array($lockName, $fieldGroupIdentifierList)) {
                $lockNames[] = $lockName;
                if ($lock->getLockedBy() === $this->getHostName()) {
                    $currentMachineLockNames[] = $lockName;
                }
            }
        }

        $concurrentAvailableSlotsCount = $this->calculateAvailableSlotsCount(
            $fieldGroupIdentifierList,
            $currentMachineLockNames,
            $input->getOption('concurrent-jobs-count')
        );

        if ($concurrentAvailableSlotsCount <= 0) {
            $output->writeln(
                sprintf(
                    '<info>Maximum concurrent jobs limit for %s is reached.</info>',
                    $channel
                )
            );
        }

        $groupFieldIdentifierListToProcess = array_slice(
            array_diff(
                $fieldGroupIdentifierList,
                $lockNames
            ),
            0,
            $input->getOption('concurrent-jobs-count')
        );

        foreach ($groupFieldIdentifierListToProcess as $identifier) {
            // Create a new dynamic channel

            $process = new \Symfony\Component\Process\Process(
                sprintf(
                    $commandline,
                    $this->getEnv($input),
                    $channel,
                    $identifier
                )
            );

            $process->setWorkingDirectory($this->getWorkingDir());
            $process->start();
        }
    }

    /**
     * @param array $lockNames
     * @param array $fieldGroupIdentifierList
     * @param int $maxConcurrentJobsCount
     *
     * @return bool
     */
    private function calculateAvailableSlotsCount($lockNames, $fieldGroupIdentifierList, $maxConcurrentJobsCount)
    {
        $concurrentJobsCount = count(array_intersect($fieldGroupIdentifierList  , $lockNames));

        return $maxConcurrentJobsCount - $concurrentJobsCount;
    }

    /**
     * @return string
     */
    private function getWorkingDir()
    {
        return $this->getContainer()->get('kernel')->getRootDir();
    }

    /**
     * @param InputInterface $input
     *
     * @return string
     */
    private function getEnv(InputInterface $input)
    {
        return $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'dev');
    }

    /**
     * @return string
     */
    private function getHostName()
    {
        return php_uname('n');
    }
}
