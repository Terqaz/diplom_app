<?php

namespace Service;

use Longman\TelegramBot\Telegram;
use VK\Client\VKApiClient;

interface BotConnectionInterface
{
    /** Получение обновлений через метод */
    public const USING_METHOD = 0;
    
    /** Получение обновлений через вебхук */
    public const USING_WEBHOOK = 1;

    public function getApi(): Telegram|VKApiClient;

    public static function getAllowedUpdates(): array;
}
