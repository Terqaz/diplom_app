<?php

namespace App\Service;

use App\Entity\Bot;
use TelegramBot\Api\BotApi;
use VK\Client\VKApiClient;

interface GetUpdatesConnectionInterface
{
    public function __construct(Bot $bot);

    public function startListening(): void;
    public function stopListening(): void;
    public function isListening(): bool;

    public function getBot(): Bot;

    public function getClient(): BotApi|VKApiClient;

    public static function getAllowedUpdates(): array;
}
