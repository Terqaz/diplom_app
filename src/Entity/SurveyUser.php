<?php

namespace App\Entity;

use App\Repository\SurveyUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyUserRepository::class)]
class SurveyUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'surveyUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BotUser $botUser = null;

    #[ORM\ManyToOne(inversedBy: 'surveyUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

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

    public function getBotUser(): ?BotUser
    {
        return $this->botUser;
    }

    public function setBotUser(?BotUser $botUser): self
    {
        $this->botUser = $botUser;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }
}
