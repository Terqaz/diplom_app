<?php

namespace App\Service\Telegram;

use App\Entity\Bot;
use App\Enum\FetchWay;
use App\Service\GetUpdatesConnectionInterface;
use TelegramBot\Api\BotApi;

class TelegramGetUpdatesConnection implements GetUpdatesConnectionInterface
{
    private Bot $bot;
    private BotApi $client;

    private bool $isListening;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;

        $this->client = new BotApi($bot->getTelegram()->getAccessToken());
    }

    public function startListening(): void
    {
        if ($this->isListening()) {
            return;
        }

        // TODO
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

    public function getClient(): BotApi
    {
        return $this->client;
    }

    public static function getAllowedUpdates(): array
    {
        return [
            Update::TYPE_MESSAGE,
            // TODO
            // Update::TYPE_CALLBACK_QUERY
        ];
    }
}
