<?php

namespace App\Service\Telegram;

use App\Dto\BotUpdate;
use App\Entity\SocialNetworkConfig;
use App\Service\BotClient;
use Exception;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class TelegramBotClient extends BotClient
{
    protected ?int $lastUpdateId = null;

    public function __construct(SocialNetworkConfig $config)
    {
        parent::__construct($config, new TelegramKeyboardMaker());

        $this->api = new BotApi($config->getAccessToken());
    }

    protected function getApi(): BotApi
    {
        return $this->api;
    }

    public function getKeyboardMaker(): TelegramKeyboardMaker
    {
        return $this->keyboardMaker;
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(int|string $chatId, string $messageText, mixed $keyboard = null): int
    {
        return $this->getApi()->sendMessage(
            chatId: $chatId,
            text: $messageText,
            replyMarkup: $keyboard
        )->getMessageId();
    }

    public function editMessage(int|string $chatId, int $messageId, ?string $messageText = null, mixed $keyboard = null): int
    {        
        if (null !== $messageId) {
            return $this->getApi()->editMessageText(
                chatId: $chatId,
                messageId: $messageId,
                text: $messageText
            )->getMessageId();
        }

        if (null !== $keyboard) {
            return $this->getApi()->editMessageReplyMarkup(
                chatId: $chatId,
                messageId: $messageId,
                replyMarkup: $keyboard
            )->getMessageId();
        }

        return 0;
    }

    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        return $this->getApi()->deleteMessage(
            $chatId,
            $messageId
        );
    }

    public function answerCallbackQuery(string $callbackId, ?int $userId = null, ?int $dialogId = null): void
    {
        $this->getApi()->answerCallbackQuery(
            callbackQueryId: $callbackId
        );
    }

    public function getAllowedUpdates(): array
    {
        return [
            'message',
            'callback_query',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getUpdates(): iterable
    {
        $updates = $this->getApi()->getUpdates(
            $this->lastUpdateId,
            timeout: self::UPDATES_TIMEOUT
        );

        foreach ($updates as &$update) {
            $this->lastUpdateId = $update->getUpdateId() + 1;
            $update = BotUpdate::fromTelegramUpdate($update);
        }

        return $updates;
    }

    public function testConnection(): bool
    {
        try {
            $this->getApi()->getMe();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
