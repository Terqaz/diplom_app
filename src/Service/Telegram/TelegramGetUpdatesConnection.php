<?php

namespace App\Service\Telegram;

use App\Entity\Bot;
use App\Enum\FetchWay;
use App\Service\GetUpdatesConnectionInterface;
use TelegramBot\Api\BotClient;

class TelegramGetUpdatesConnection implements GetUpdatesConnectionInterface
{
    private Bot $bot;
    private BotClient $client;

    private bool $isListening;

    public function __construct(Bot $bot)
    {
        $this->bot = $bot;

        $this->client = new BotClient($bot->getTelegram()->getAccessToken());
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

    public function getClient(): BotClient
    {
        return $this->client;
    }

    public static function getAllowedUpdates(): array
    {
        return [
            "message"
            // TODO
            // Update::TYPE_CALLBACK_QUERY
        ];
    }
}
