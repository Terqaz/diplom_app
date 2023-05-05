<?php

namespace App\Command;

use Amp;
use Amp\DeferredCancellation;
use Amp\Future;
use App\Dto\BotUpdate;
use App\Entity\SocialNetworkConfig;
use App\Enum\SocialNetworkCode;
use App\Repository\SocialNetworkConfigRepository;
use App\Service\BotClient;
use App\Service\ConnectionsManager;
use App\Service\Telegram\TelegramBotClient;
use App\Service\UpdatesHandler;
use Closure;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Revolt\EventLoop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

use function Amp\delay;

#[AsCommand(
    name: 'bots:long-poll',
)]
class GetUpdatesLongPollCommand extends Command
{
    const CONFIGS_REFRESH_SECONDS = 5;

    private SocialNetworkConfigRepository $socialNetworkConfigRepository;
    private UpdatesHandler $updatesHandler;

    private OutputInterface $output;
    private SymfonyStyle $io;

    /** 
     * Клиенты по id
     * 
     * @var array<int, BotClient> 
     */
    private array $clients = [];

    public function __construct(SocialNetworkConfigRepository $socialNetworkConfigRepository, UpdatesHandler $updatesHandler)
    {
        $this->socialNetworkConfigRepository = $socialNetworkConfigRepository;
        $this->updatesHandler = $updatesHandler;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Start listening updates');
        $this->refreshClients();

        $suspension = EventLoop::getSuspension();

        EventLoop::repeat(self::CONFIGS_REFRESH_SECONDS, function (): void {
            $this->refreshClients();
        });

        $futures = [];

        EventLoop::repeat(0.01, function () use ($futures): void {
            foreach ($this->clients as $client) {
                if (isset($futures[$client->getConfig()->getId()])) {
                    continue;
                }

                $futures[$client->getConfig()->getId()] = Amp\async(function () use ($client): int {
                    try {
                        foreach ($client->getUpdates() as $update) {
                            $update->setBotId($client->getConfig()->getBot()->getId());

                            $this->updatesHandler->processUpdate($client, $update);
                        }
                        echo 'End ' . $client->getConfig()->getCode() . ' ' . $client->getConfig()->getId() . "\n";
                    } catch (Throwable $e) {
                        $this->io->error([
                            $e->getMessage(),
                            $e->getTraceAsString()
                        ]);
                    }

                    return $client->getConfig()->getId();
                });
            }

            if (count($futures) > 0) {
                $configId = Future\awaitFirst($futures);
                unset($futures[$configId]);
            }
        });

        $suspension->suspend();

        return Command::SUCCESS;
    }

    private function refreshClients(): void
    {
        $newConfigs = $this->socialNetworkConfigRepository->findBy(['isEnabled' => true]);

        $newConfigsById = [];

        foreach ($newConfigs as $config) {
            $newConfigsById[$config->getId()] = $config;
        }

        $newClients = [];

        // удаляем старых клиентов
        foreach ($this->clients as $client) {
            $config = $client->getConfig();

            // Если удален или отключен
            if (!isset($newConfigsById[$config->getId()]) || !$config->isEnabled()) {
                $config->setIsActive(false);
                $this->socialNetworkConfigRepository->save($config, true);

                $this->output->writeln('Stop listening connection ' . $config->getConnectionId() . ' by config id=' . $config->getId());
                continue;
            }

            $newClients[$config->getId()] = $client;
        }

        // создаем новых клиентов и запускаем обработку
        foreach ($newConfigsById as $configId => $config) {
            if (isset($newClients[$configId]) && $config->isActive()) {
                continue;
            }

            $client = BotClient::createByCode($config);
            $newClients[$config->getId()] = $client;

            $config->setIsActive(true);
            $this->socialNetworkConfigRepository->save($config, true);

            $this->output->writeln('Start listening connection ' . $config->getConnectionId() . ' by config id=' . $config->getId());
        }

        $this->clients = $newClients;
        $this->output->writeln('Update listeners refreshed. Active: ' . count($this->clients));
    }
}
