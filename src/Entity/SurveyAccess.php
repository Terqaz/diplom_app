<?php

namespace App\Entity;

use App\Repository\SurveyAccessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyAccessRepository::class)]
class SurveyAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $propertyName = null;

    #[ORM\ManyToOne(inversedBy: 'surveyAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Respondent $respondent = null;

    #[ORM\ManyToOne(inversedBy: 'surveyAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;

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
