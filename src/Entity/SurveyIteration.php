<?php

namespace App\Entity;

use App\Repository\SurveyIterationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyIterationRepository::class)]
class SurveyIteration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isSurveyChanged = false;

    #[ORM\ManyToOne(inversedBy: 'surveyIterations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function isIsSurveyChanged(): ?bool
    {
        return $this->isSurveyChanged;
    }

    public function setIsSurveyChanged(bool $isSurveyChanged): self
    {
        $this->isSurveyChanged = $isSurveyChanged;

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
