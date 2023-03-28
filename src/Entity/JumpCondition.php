<?php

namespace App\Entity;

use App\Repository\JumpConditionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JumpConditionRepository::class)]
class JumpCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $serialNumber = null;

    #[ORM\ManyToOne(inversedBy: 'jumpConditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\ManyToOne(inversedBy: 'jumpConditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $toQuestion = null;

    #[ORM\OneToMany(mappedBy: 'jumpCondition', targetEntity: Subcondition::class, orphanRemoval: true)]
    private Collection $subconditions;

    public function __construct()
    {
        $this->subconditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): ?int
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(int $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

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

    /**
     * @return Collection<int, Subcondition>
     */
    public function getSubconditions(): Collection
    {
        return $this->subconditions;
    }

    public function addSubcondition(Subcondition $subcondition): self
    {
        if (!$this->subconditions->contains($subcondition)) {
            $this->subconditions->add($subcondition);
            $subcondition->setJumpCondition($this);
        }

        return $this;
    }

    public function removeSubcondition(Subcondition $subcondition): self
    {
        if ($this->subconditions->removeElement($subcondition)) {
            // set the owning side to null (unless already changed)
            if ($subcondition->getJumpCondition() === $this) {
                $subcondition->setJumpCondition(null);
            }
        }

        return $this;
    }

    public function getToQuestion(): ?Question
    {
        return $this->toQuestion;
    }

    public function setToQuestion(?Question $toQuestion): self
    {
        $this->toQuestion = $toQuestion;

        return $this;
    }
}
