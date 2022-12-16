<?php

namespace App\Entity;

use App\Repository\RespondentAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentAccessRepository::class)]
class RespondentAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $property = null;

    #[ORM\ManyToOne(inversedBy: 'surveyAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Respondent $respondent = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;

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
