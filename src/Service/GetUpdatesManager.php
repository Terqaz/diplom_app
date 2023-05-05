<?php

namespace App\Service;

use App\Entity\Bot;
use App\Entity\SocialNetworkConfig;
use App\Enum\FetchWay;
use App\Repository\BotRepository;
use Doctrine\ORM\EntityManagerInterface;

class GetUpdatesManager
{
    private const CONNECTION_CLASSES = [
        SocialNetworkConfig::TELEGRAM_CODE => TelegramGetUpdatesConnection::class,
        SocialNetworkConfig::VKONTAKTE_CODE => VkontakteGetUpdatesConnection::class,
    ];

    private EntityManagerInterface $em;

    /**
     * [...socialNetworkConfigId => GetUpdatesConnectionInterface]
     * 
     * @var array<int, GetUpdatesConnectionInterface>
     */
    private array $connections;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->connections = [];
    }

    public function createConnections(): void
    {
        /** @var BotRepository $botRepository */
        $botRepository = $this->em->getRepository(Bot::class);

        foreach ($botRepository->findAll() as $bot) {
            foreach ($bot->getSocialNetworkConfigs() as $network) {
                $this->addConnection($bot, $network);
            }
        }
    }

    public function addConnection(Bot $bot, SocialNetworkConfig $network): void
    {
        $class = self::CONNECTION_CLASSES[$network];

        /** @var GetUpdatesConnectionInterface $connection */
        $connection = (new $class($bot));
        $this->connections[$network->getId()] = $connection;

        $connection->startListening();
    }

    public function removeConnections(): void
    {
        foreach ($this->connections as $socialNetworkConfigId => $_) {
            $this->removeConnection($socialNetworkConfigId);
        }
    }

    public function removeConnection(int $socialNetworkConfigId): void
    {
        $this->connections[$socialNetworkConfigId]->stopListening();

        unset($this->connections[$socialNetworkConfigId]);
    }
}
