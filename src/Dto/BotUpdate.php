<?php

namespace App\Dto;

use App\Entity\SocialNetworkConfig;
use LogicException;
use TelegramBot\Api\Types\Update;

class BotUpdate
{
    private string $socialNetworkCode;
    private int|string $chatId;
    private string $messageText;

    private int $botId;

    protected function __construct(string $socialNetworkCode, int|string $chatId, string $messageText) {
        $this->socialNetworkCode = $socialNetworkCode;
        $this->chatId = $chatId;
        $this->messageText = $messageText;
    }

    public static function fromTelegramUpdate(Update $update): self
    {
        if (null === $update->getMessage()) {
            throw new LogicException("Only new messages supported");
        }

        $message = $update->getMessage();

        return new self(
            SocialNetworkConfig::TELEGRAM_CODE,
            $message->getChat()->getId(),
            $message->getText()
        );
    }

    public function getSocialNetworkCode(): string
    {
        return $this->socialNetworkCode;
    }

    public function getChatId(): int|string
    {
        return $this->chatId;
    }

    public function getMessageText(): string
    {
        return $this->messageText;
    }

    public function getBotId(): int
    {
        return $this->botId;
    }

    public function setBotId(int $botId): self
    {
        $this->botId = $botId;

        return $this;
    }
}
