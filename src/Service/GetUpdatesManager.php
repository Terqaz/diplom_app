<?php

namespace App\Service;

use App\Entity\Bot;
use App\Entity\SocialNetwork;
use App\Enum\FetchWay;
use App\Repository\BotRepository;
use Doctrine\ORM\EntityManagerInterface;

class GetUpdatesManager
{
    private const CONNECTION_CLASSES = [
        SocialNetwork::TELEGRAM_CODE => TelegramGetUpdatesConnection::class,
        SocialNetwork::VKONTAKTE_CODE => VkontakteGetUpdatesConnection::class,
    ];

    private EntityManagerInterface $em;

    /**
     * [...socialNetworkId => GetUpdatesConnectionInterface]
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
            foreach ($bot->getSocialNetworks() as $network) {
                $this->addConnection($bot, $network);
            }
        }
    }

    public function addConnection(Bot $bot, SocialNetwork $network): void
    {
        $class = self::CONNECTION_CLASSES[$network];

        /** @var GetUpdatesConnectionInterface $connection */
        $connection = (new $class($bot));
        $this->connections[$network->getId()] = $connection;

        $connection->startListening();
    }

    public function removeConnections(): void
    {
        foreach ($this->connections as $socialNetworkId => $_) {
            $this->removeConnection($socialNetworkId);
        }
    }

    public function removeConnection(int $socialNetworkId): void
    {
        $this->connections[$socialNetworkId]->stopListening();

        unset($this->connections[$socialNetworkId]);
    }
}
