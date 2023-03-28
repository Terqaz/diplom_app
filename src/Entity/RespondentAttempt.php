<?php

namespace App\Entity;

use App\Repository\RespondentAttemptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentAttemptRepository::class)]
class RespondentAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addingDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $testScore = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAttempts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Respondent $respondent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddingDate(): ?\DateTimeInterface
    {
        return $this->addingDate;
    }

    public function setAddingDate(\DateTimeInterface $addingDate): self
    {
        $this->addingDate = $addingDate;

        return $this;
    }

    public function getTestScore(): ?float
    {
        return $this->testScore;
    }

    public function setTestScore(?float $testScore): self
    {
        $this->testScore = $testScore;

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

    public function getRespondent(): ?Respondent
    {
        return $this->respondent;
    }

    public function setRespondent(?Respondent $respondent): self
    {
        $this->respondent = $respondent;

        return $this;
    }
}
