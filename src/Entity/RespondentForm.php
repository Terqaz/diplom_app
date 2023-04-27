<?php

namespace App\Entity;

use App\Repository\RespondentFormRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentFormRepository::class)]
class RespondentForm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Дата отправки анкеты */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $addingDate = null;

    #[ORM\ManyToOne(inversedBy: 'respondentForms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\ManyToOne(inversedBy: 'respondentForms')]
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
