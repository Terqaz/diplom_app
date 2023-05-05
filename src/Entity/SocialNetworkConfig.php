<?php

namespace App\Entity;

use App\Enum\SocialNetworkCode;
use App\Repository\SocialNetworkConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SocialNetworkConfigRepository::class)]
class SocialNetworkConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Код соц сети */
    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: SocialNetworkCode::TYPES)]
    #[Groups(['connectionsEdit'])]
    private ?string $code = null;

    /**
     * Если ВКонтакте, то id сообщества.
     * Если Telegram, то имя бота
     */
    #[ORM\Column(length: 32)]
    #[Assert\Length(
        min: 1,
        max: 32
    )]
    #[Groups(['connectionsEdit'])]
    private ?string $connectionId = null;

    /** Включено ли подключение пользователем */
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['connectionsEdit'])]
    private ?bool $isEnabled = false;

    /** Идет ли процесс получения обновлений через вебхук или getUpdates */
    #[ORM\Column(options: ['default' => false])]
    private ?bool $isActive = false;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 40,
        max: 255
    )]
    #[Groups(['connectionsEdit'])]
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

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
