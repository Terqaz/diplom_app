<?php

namespace App\Service;

use App\Dto\BotUpdate;
use App\Entity\SocialNetworkConfig;
use App\Enum\SocialNetworkCode;
use App\Service\Telegram\TelegramBotClient;
use App\Service\Telegram\TelegramKeyboardMaker;
use App\Service\Vk\VkBotClient;
use App\Service\Vk\VkKeyboardMaker;

/**
 * API с привязкой к боту
 */
abstract class BotClient
{
    /** Только для Телеграм */
    public const UPDATES_LIMIT = 5;

    public const UPDATES_TIMEOUT = 3;

    private const CLIENT_CLASSES = [
        SocialNetworkCode::TELEGRAM => TelegramBotClient::class,
        SocialNetworkCode::VKONTAKTE => VkBotClient::class,
    ];

    private const KEYBOARD_MAKER_CLASSES = [
        SocialNetworkCode::TELEGRAM => TelegramKeyboardMaker::class,
        SocialNetworkCode::VKONTAKTE => VkKeyboardMaker::class,
    ];

    protected SocialNetworkConfig $config;
    protected mixed $api;
    protected KeyboardMaker $keyboardMaker;

    public function __construct(SocialNetworkConfig $config, KeyboardMaker $keyboardMaker)
    {
        $this->config = $config;
        $this->keyboardMaker = $keyboardMaker;
    }

    // todo вынести в фабрику
    final public static function createByCode(SocialNetworkConfig $config): BotClient
    {
        $clientClass = self::CLIENT_CLASSES[$config->getCode()];
        $keyboardMakerClass = self::KEYBOARD_MAKER_CLASSES[$config->getCode()];

        return new $clientClass($config, new $keyboardMakerClass());
    }

    public function getConfig(): SocialNetworkConfig
    {
        return $this->config;
    }

    abstract protected function getApi(): mixed;

    abstract public function getKeyboardMaker(): KeyboardMaker;

    abstract public function testConnection(): bool;

    abstract public function getAllowedUpdates(): array;

    /**
     * @return BotUpdate[]
     */
    abstract public function getUpdates(): iterable;

    abstract public function sendMessage(int|string $chatId, string $messageText, mixed $keyboard = null): mixed;

    abstract public function editMessage(int|string $chatId, int $messageId, ?string $messageText = null, mixed $keyboard = null): mixed;

    abstract public function deleteMessage(int|string $chatId, int $messageId): bool;

    abstract public function answerCallbackQuery(string $callbackId, ?int $userId, ?int $dialogId): void;
}
