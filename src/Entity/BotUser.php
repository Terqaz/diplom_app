<?php

namespace App\Entity;

use App\Repository\BotUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BotUserRepository::class)]
class BotUser
{
    public const ADMIN = 'admin'; // Администратор бота
    public const QUESTIONER = 'questioner'; // Анкетер
    public const VIEWER = 'viewer'; // Просматривающий результаты

    public const ROLES = [
        self::ADMIN,
        self::QUESTIONER,
        self::VIEWER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'botUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bot $bot = null;

    #[ORM\ManyToOne(inversedBy: 'botUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

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

    public function getUserData(): ?User
    {
        return $this->userData;
    }

    public function setUserData(?User $userData): self
    {
        $this->userData = $userData;

        return $this;
    }
}
