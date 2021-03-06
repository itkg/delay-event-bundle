<?php

namespace Itkg\DelayEventBundle\Command;

use Itkg\DelayEventBundle\Handler\LockHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UnlockCommand
 */
class UnlockCommand extends ContainerAwareCommand
{
    /**
     * @var LockHandlerInterface
     */
    private $lockHandler;

    /**
     * @param LockHandlerInterface $lockHandler
     * @param null|string          $name
     */
    public function __construct(
        LockHandlerInterface $lockHandler, $name = null)
    {
        $this->lockHandler = $lockHandler;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('itkg_delay_event:unlock')
            ->setDescription('Unlock command')
            ->addOption(
                'channel',
                'c',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Specify the channels to unlock (default: [\'default\'])',
                ['default']
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($input->getOption('channel') as $channel) {
            $this->lockHandler->release($channel);
            $output->writeln(sprintf(
                'Channel <info>%s</info> unlocked.',
                $channel
            ));
        }
    }
}
