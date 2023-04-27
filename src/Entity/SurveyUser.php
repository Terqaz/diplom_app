<?php

namespace App\Entity;

use App\Repository\SurveyUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SurveyUserRepository::class)]
class SurveyUser
{
    /** Анкетер */
    public const QUESTIONER = 'questioner';
    /** Просматривающий результаты */
    public const VIEWER = 'viewer';

    public const ROLES = [
        self::QUESTIONER,
        self::VIEWER,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: SurveyUser::ROLES)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'surveyUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\ManyToOne(inversedBy: 'surveyUsers')]
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

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

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
