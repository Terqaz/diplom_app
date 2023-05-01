<?php

namespace App\Service;

use App\Entity\Bot;
use Longman\TelegramBot\Telegram;
use VK\Client\VKApiClient;

interface BotConnectionInterface
{
    public function __construct(Bot $bot, int $getUpdatesWay);

    public function startListening(): void;
    public function stopListening(): void;
    public function isListening(): bool;

    public function getBot(): Bot;

    public function getClient(): Telegram|VKApiClient;

    public static function getAllowedUpdates(): array;
}
