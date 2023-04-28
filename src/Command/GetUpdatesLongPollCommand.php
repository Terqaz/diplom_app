<?php

namespace App\Command;

use Service\Telegram\TelegramConnection;
use Service\Vk\VkontakteService;
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
    private TelegramConnection $tg;
    private VkontakteService $vk;

    // public function __construct() {
    //     parent::__construct();

    //     // $this->tg = $tg;
    //     // $this->vk = $vk;
    // }

    // protected function configure(): void
    // {
    //     $this
    //         ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
    //         ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    // }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        

        return Command::SUCCESS;
    }
}
