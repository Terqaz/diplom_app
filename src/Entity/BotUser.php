<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\BotUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BotUserRepository::class)]
class BotUser
{
    /** Возможные роли */
    public const ROLES = [
        UserRole::ADMIN,
        UserRole::QUESTIONER,
        UserRole::VIEWER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['userAccessesEdit'])]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: BotUser::ROLES)]
    #[Groups(['userAccessesEdit'])]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bot $bot = null;

    #[ORM\ManyToOne(inversedBy: 'botUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['userAccessesEdit'])]
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
