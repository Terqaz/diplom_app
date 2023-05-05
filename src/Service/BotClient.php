<?php

namespace App\Service;

/**
 * API с привязкой к боту
 */
abstract class BotClient
{
    /** Только для Телеграм */
    public const UPDATES_LIMIT = 5;
    
    public const UPDATES_TIMEOUT = 25;

    protected mixed $api;

    protected ?int $lastUpdateId = null;

    abstract public function __construct(string $accessToken);

    abstract protected function getApi(): mixed;

    /**
     * @param integer|null $offset
     * для ВК - номер последнего события, 
     * для Телеграм - номер следующего. Если null, то автоматически получаются новые обновления
     * @return BotUpdate[]
     */
    abstract public function getUpdates(int $offset = null): array;

    /**
     * @param string $messageText
     * @param string[][]|null $keyboard
     * @return void
     */
    public abstract function sendMessage(int|string $chatId, string $messageText, ?array $keyboard = null): void;
}
