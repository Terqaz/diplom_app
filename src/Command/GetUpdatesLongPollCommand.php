<?php

namespace App\Command;

use Amp;
use Amp\Future;
use App\Dto\BotUpdate;
use App\Entity\SocialNetworkConfig;
use App\Repository\SocialNetworkConfigRepository;
use App\Service\BotClient;
use App\Service\ConnectionsManager;
use App\Service\Telegram\TelegramBotClient;
use Doctrine\ORM\EntityManagerInterface;
use Revolt\EventLoop;
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
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var SocialNetworkConfigRepository $socialNetworkConfigRepository */
        $socialNetworkConfigRepository = $this->em->getRepository(SocialNetworkConfig::class);

        $telegramConfigs = $socialNetworkConfigRepository->findBy(['code' => SocialNetworkConfig::TELEGRAM_CODE]);
        $vkConfigs = $socialNetworkConfigRepository->findBy(['code' => SocialNetworkConfig::VKONTAKTE_CODE]);

        foreach ($telegramConfigs as $config) {
            $client = new TelegramBotClient($config->getAccessToken());

            $longPollsData[] = [$client, $config];
        }

        while (true) {
            $futures = [];
            try {
                foreach ($longPollsData as $longPollData) {
                    $futures[] = Amp\async(
                        function () use ($longPollData) {
                            /** 
                             * @var BotClient $client
                             * @var SocialNetworkConfig $config
                             */
                            [$client, $config] = $longPollData;
    
                            $updates = $client->getUpdates();
                            // Указываем из какого бота пришло обновление
                            foreach ($updates as $update) {
                                $update->setBotId($config->getBot()->getId());
                            }
                        }
                    );
                }
            } catch (\Throwable $th) {
                // If any one of the requests fails the combo will fail
                continue;
            }
            

            /** @var BotUpdate[][] $update */
            $updates = Future\await($futures);
        }

        try {
            $responses = Future\await(array_map(function ($uri) use ($httpClient) {
                return Amp\async(fn () => $httpClient->request(new Request($uri, 'HEAD')));
            }, $uris));

            foreach ($responses as $key => $response) {
                printf(
                    "%s | HTTP/%s %d %s\n",
                    $key,
                    $response->getProtocolVersion(),
                    $response->getStatus(),
                    $response->getReason()
                );
            }
        } catch (Exception $e) {
            // If any one of the requests fails the combo will fail
            echo $e->getMessage(), "\n";
        }

        return Command::SUCCESS;
    }
}
