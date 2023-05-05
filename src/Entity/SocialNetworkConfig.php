<?php

namespace App\Entity;

use App\Repository\SocialNetworkConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocialNetworkConfigRepository::class)]
class SocialNetworkConfig
{
    public const TELEGRAM_CODE = 'tg';
    public const VKONTAKTE_CODE = 'vk';

    public const CODES = [
        self::TELEGRAM_CODE,
        self::VKONTAKTE_CODE,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Код соц сети */
    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: SocialNetworkConfig::CODES)]
    private ?string $code = null;

    /**
     * Если ВКонтакте, то id сообщества.
     * Если Telegram, то имя бота
     */
    #[ORM\Column(length: 32)]
    private ?string $connectionId = null;

    // TODO
    // 1871965994:AAFDTvdixnVqXTn-_d0UWp8GPW_O2D7b-mU
    // 088110c08bec03540876c1f0c15cc8f5bc2934fe2fee055941d1c88487ac848151f1f65c65c89592fd45d

    #[ORM\Column(length: 255)]
    private ?string $accessToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $webhookUrlPath = null;

    #[ORM\ManyToOne(inversedBy: 'socialNetworkConfigs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bot $bot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getWebhookUrlPath(): ?string
    {
        return $this->webhookUrlPath;
    }

    public function setWebhookUrlPath(?string $webhookUrlPath): self
    {
        $this->webhookUrlPath = $webhookUrlPath;

        return $this;
    }

    public function getBot(): ?Bot
    {
        return $this->bot;
    }

    public function setBot(?Bot $bot): self
    {
        $this->bot = $bot;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getConnectionId(): ?string
    {
        return $this->connectionId;
    }

    public function setConnectionId(string $connectionId): self
    {
        $this->connectionId = $connectionId;

        return $this;
    }
}
