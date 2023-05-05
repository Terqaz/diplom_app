<?php

namespace App\Service\Telegram;

use App\Dto\BotUpdate;
use App\Service\BotClient;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramBotClient extends BotClient
{
    public function __construct(string $accessToken)
    {
        $this->api = new BotApi($accessToken);
    }

    protected function getApi(): BotApi
    {
        return $this->api;
    }

    public function getUpdates(int $offset = null): array
    {
        if (null === $offset) {
            $offset = $this->lastUpdateId + 1;
        }

        $updates = $this->getApi()->getUpdates(
            $offset,
            self::UPDATES_LIMIT,
            self::UPDATES_TIMEOUT
        );

        foreach ($updates as &$update) {
            $update = BotUpdate::fromTelegramUpdate($update);
        }

        $this->lastUpdateId = $offset + count($updates);

        return $updates;
    }

    public function sendMessage(int|string $chatId, string $messageText, ?array $keyboard = null): void
    {
        if (null !== $keyboard) {
            $keyboard = new ReplyKeyboardMarkup($keyboard);
        }

        $this->getApi()->sendMessage(
            chatId: $chatId,
            text: $messageText,
            replyMarkup: $keyboard
        );
    }
}
