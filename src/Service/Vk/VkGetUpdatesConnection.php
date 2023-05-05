<?php

namespace App\Service\Vk;

use App\Entity\Bot;
use App\Service\GetUpdatesConnectionInterface;
use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
use VK\Client\VKApiClient;

class VkGetUpdatesConnection implements GetUpdatesConnectionInterface
{
    private Bot $bot;
    private VKApiClient $client;

    private bool $isListening;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;

        $this->client = new VKApiClient();
    }

    public function startListening(): void
    {
        if ($this->isListening()) {
            return;
        }

        $network = $this->bot->getVkontakte();

        $params = array_merge([
            'group_id' => (int) $network->getConnectionId(),
            'enabled' => 1,
        ], self::getAllowedUpdates());

        $this->client->groups()->setLongPollSettings(
            $network->getAccessToken(),
            $params
        );

        $handler = new VkGetUpdatesHandler();
        $executor = new VKCallbackApiLongPollExecutor(
            $this->client,
            $network->getAccessToken(),
            (int) $network->getConnectionId(),
            $handler,
            wait: 25
        );

        $executor->listen();

        $this->isListening = true;
    }

    public function stopListening(): void
    {
        if (!$this->isListening()) {
            return;
        }

        $this->isListening = false;

        // TODO
    }

    public function isListening(): bool
    {
        return $this->isListening;
    }

    public function getBot(): Bot
    {
        return $this->bot;
    }

    public function getClient(): VKApiClient
    {
        return $this->client;
    }

    public static function getAllowedUpdates(): array
    {
        return [
            'message_new' => 1,
            'wall_post_new' => 1,
        ];
    }
}
