<?php

namespace App\Entity;

use App\Repository\BotUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BotUserRepository::class)]
class BotUser
{
    public const ADMIN = 'admin';
    public const QUESTIONER = 'questioner';
    public const RESULTS_VIEWER = 'viewer';

    public const ROLES = [
        self::ADMIN,
        self::QUESTIONER,
        self::RESULTS_VIEWER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'botUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bot $bot = null;

    #[ORM\ManyToOne(inversedBy: 'botUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userData = null;

    #[ORM\OneToMany(mappedBy: 'botUser', targetEntity: SurveyUser::class)]
    private Collection $surveyUsers;

    public function __construct()
    {
        $this->surveyUsers = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, SurveyUser>
     */
    public function getSurveyUsers(): Collection
    {
        return $this->surveyUsers;
    }

    public function addSurveyUser(SurveyUser $surveyUser): self
    {
        if (!$this->surveyUsers->contains($surveyUser)) {
            $this->surveyUsers->add($surveyUser);
            $surveyUser->setBotUser($this);
        }

        return $this;
    }

    public function removeSurveyUser(SurveyUser $surveyUser): self
    {
        if ($this->surveyUsers->removeElement($surveyUser)) {
            // set the owning side to null (unless already changed)
            if ($surveyUser->getBotUser() === $this) {
                $surveyUser->setBotUser(null);
            }
        }

        return $this;
    }
}
