<?php

namespace App\Command;

use App\Service\ConnectionsManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'bots:long-poll',
)]
class GetUpdatesLongPollCommand extends Command
{
    private ConnectionsManager $connectionsManager;

    public function __construct(ConnectionsManager $connectionsManager) {
        $this->connectionsManager = $connectionsManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->connectionsManager->createConnections();

        return Command::SUCCESS;
    }
}
