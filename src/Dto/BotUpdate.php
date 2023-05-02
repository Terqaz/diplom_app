<?php

namespace App\Dto;

use App\Entity\SocialNetwork;
use LogicException;
use TelegramBot\Api\Types\Update;

class BotUpdate
{
    private string $socialNetwork;
    private int|string $userId;
    private string $messageText;

    protected function __construct(string $socialNetwork, int|string $userId, string $messageText) {
        $this->socialNetwork = $socialNetwork;
        $this->userId = $userId;
        $this->messageText = $messageText;
    }

    public static function fromTelegramUpdate(Update $update): self
    {
        if (null === $update->getMessage()) {
            throw new LogicException("Only new messages supported");
        }

        $message = $update->getMessage();

        return new self(
            SocialNetwork::TELEGRAM_CODE,
            $message->getChat()->getId(),
            $message->getText()
        );
    }

    public function getChatId(): int|string
    {
        return $this->userId;
    }

    public function getMessageText(): string
    {
        return $this->messageText;
    }
}
