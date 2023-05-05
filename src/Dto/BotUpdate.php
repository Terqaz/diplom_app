<?php

namespace App\Dto;

use App\Entity\SocialNetworkConfig;
use App\Enum\SocialNetworkCode;
use LogicException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use TelegramBot\Api\Types\Update;

class BotUpdate
{
    public const NEW_MESSAGE = 'new_message';
    public const CALLBACK = 'callback';

    private string $socialNetworkCode;
    private int|string $fromId;
    private string $type;

    private ?string $messageText = null;
    private ?string $callbackId = null;
    private ?string $callbackMessageId = null;
    private ?string $peerId = null;
    private ?string $callbackData = null;

    private int $botId;

    protected function __construct(string $socialNetworkCode, int|string $fromId, string $type) {
        $this->socialNetworkCode = $socialNetworkCode;
        $this->fromId = $fromId;
        $this->type = $type;
    }

    public static function fromTelegramUpdate(Update $update): self
    {
        if (null !== $update->getMessage()) {
            $message = $update->getMessage();

            $update = new self(
                SocialNetworkCode::TELEGRAM,
                $message->getChat()->getId(),
                self::NEW_MESSAGE
            );
            $update->messageText = $message->getText();
        } else if (null !== $update->getCallbackQuery()) {
            $callback = $update->getCallbackQuery();

            $update = new self(
                SocialNetworkCode::TELEGRAM,
                $callback->getFrom()->getId(),
                self::CALLBACK
            );
            $update->callbackId = $callback->getId();
            $update->callbackMessageId = $callback->getInlineMessageId();
            $update->callbackData = $callback->getData();
        } else {
            throw new LogicException("Unsupported update");
        }
        
        return $update;
    }

    public static function fromVkUpdate(array $update): self
    {
        if ($update['type'] === 'message_new') {
            $message = $update['object']['message'];

            $update = new self(
                SocialNetworkCode::VKONTAKTE,
                $message['from_id'],
                self::NEW_MESSAGE
            );
            $update->messageText = $message['text'];
        } else if ($update['type'] === 'message_event') {
            $callback = $update['object'];

            $update = new self(
                SocialNetworkCode::VKONTAKTE,
                $callback['user_id'],
                self::NEW_MESSAGE
            );
            
            $update->callbackId = $callback['event_id'];
            $update->callbackMessageId = $callback['conversation_message_id'];
            $update->peerId = $callback['peer_id'];
            $update->callbackData = $callback['payload']['data'];
        } else {
            throw new LogicException("Unsupported update");
        }

        return $update;
    }

    public function getSocialNetworkCode(): string
    {
        return $this->socialNetworkCode;
    }

    public function getFromId(): int|string
    {
        return $this->fromId;
    }

    public function getMessageText(): ?string
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

    public function getType(): string
    {
        return $this->type;
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackData;
    }

    public function getCallbackMessageId(): ?string
    {
        return $this->callbackMessageId;
    }

    public function getCallbackId(): ?string
    {
        return $this->callbackId;
    }

    public function getPeerId(): ?string
    {
        return $this->peerId;
    }
}
