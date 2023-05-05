<?php

namespace App\Entity;

use App\Repository\JumpConditionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

#[ORM\Entity(repositoryClass: JumpConditionRepository::class)]
class JumpCondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Порядковый номер среди всех ответов и условий перехода */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $serialNumber = null;

    #[ORM\ManyToOne(inversedBy: 'jumpConditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\ManyToOne(inversedBy: 'jumpConditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $toQuestion = null;

    /**
     * @var Collection<Subcondition>
     */
    #[ORM\OneToMany(mappedBy: 'jumpCondition', targetEntity: Subcondition::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[OrderBy(['serialNumber' => 'ASC'])]
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

    public function isJumpApplying(array $choosedAnswers): bool
    {
        $isJump = true;
        /** @var Subcondition $subcondition */
        foreach ($this->subconditions as $subcondition) {
            $question = $subcondition->getAnswerVariant()->getQuestion();

            // Если ответы не даны, то игнорим условие перехода 
            $answers = $choosedAnswers[$question->getId()] ?? [];

            if ($subcondition->isEqual()) {
                // Хотя бы один ответ должен совпадать
                if (!in_array($subcondition->getAnswerVariant()->getId(), $answers)) {
                    $isJump = false;
                }
            } else {
                // Никакой ответ не должен совпадать
                if (in_array($subcondition->getAnswerVariant()->getId(), $answers)) {
                    $isJump = false;
                }
            }
        }

        return $isJump;
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
