<?php

namespace App\Service\Vk;

use App\Entity\Bot;
use App\Entity\SocialNetwork;
use App\Service\BotConnectionInterface;
use VK\CallbackApi\LongPoll\VKCallbackApiLongPollExecutor;
use VK\Client\VKApiClient;

class VkontakteConnection implements BotConnectionInterface
{
    private Bot $bot;
    private VKApiClient $client;

    private bool $isListening;
    private int $getUpdatesWay;

    public function __construct(Bot $bot, int $getUpdatesWay = SocialNetwork::WEBHOOK_UPDATES)
    {
        $this->bot = $bot;
        $this->getUpdatesWay = $getUpdatesWay;

        $this->client = new VKApiClient();

        if ($getUpdatesWay === SocialNetwork::METHOD_UPDATES) {
            $this->initGetUpdates();
        } else if ($getUpdatesWay === SocialNetwork::WEBHOOK_UPDATES) {
            $this->initCallback();
        }
    }

    //088110c08bec03540876c1f0c15cc8f5bc2934fe2fee055941d1c88487ac848151f1f65c65c89592fd45d

    private function initGetUpdates(): void
    {
    }

    public function startListening(): void
    {
        if ($this->isListening()) {
            return;
        }

        $network = $this->bot->getVkontakte();

        if ($this->getUpdatesWay === SocialNetwork::METHOD_UPDATES) {
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
        }

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

    public function isListening(): bool {
        return $this->isListening;
    }

    private function initCallback(): void
    {
        // TODO
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
