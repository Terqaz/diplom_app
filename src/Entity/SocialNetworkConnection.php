<?php

namespace App\Entity;

use App\Repository\SocialNetworkConnectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialNetworkConnectionRepository::class)]
class SocialNetworkConnection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $accessToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $webhookUrlPath = null;

    #[ORM\ManyToOne(inversedBy: 'socialNetworkConnections')]
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
}
