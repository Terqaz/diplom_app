<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $serialNumber = null;

    #[ORM\Column(length: 400, unique: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $correctAnswer = null;

    // Если через запятую, то границы интервалов шкалы
    // Если 2 числа через тире, то границы вводимого числа
    // Границы всегда включаются в интервал
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $intervalBorders = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isRequired = true;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $canGiveOwnAnswer = false;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $maxVariants = null;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: AnswerVariant::class)]
    private Collection $variants;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: RespondentAnswer::class)]
    private Collection $respondentAnswers;

    #[ORM\OneToMany(mappedBy: 'toQuestion', targetEntity: JumpCondition::class, orphanRemoval: true)]
    private Collection $jumpConditions;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->respondentAnswers = new ArrayCollection();
        $this->jumpConditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, AnswerVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(AnswerVariant $answer): self
    {
        if (!$this->variants->contains($answer)) {
            $this->variants->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeVariant(AnswerVariant $answer): self
    {
        if ($this->variants->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }

    public function getCorrectAnswer(): ?string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(?string $correctAnswer): self
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    /**
     * @return Collection<int, RespondentAnswer>
     */
    public function getRespondentAnswers(): Collection
    {
        return $this->respondentAnswers;
    }

    public function addRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if (!$this->respondentAnswers->contains($respondentAnswer)) {
            $this->respondentAnswers->add($respondentAnswer);
            $respondentAnswer->setQuestion($this);
        }

        return $this;
    }

    public function removeRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if ($this->respondentAnswers->removeElement($respondentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($respondentAnswer->getQuestion() === $this) {
                $respondentAnswer->setQuestion(null);
            }
        }

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

    public function getIntervalBorders(): ?string
    {
        return $this->intervalBorders;
    }

    public function setIntervalBorders(?string $intervalBorders): self
    {
        $this->intervalBorders = $intervalBorders;

        return $this;
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

    /**
     * @return Collection<int, JumpCondition>
     */
    public function getJumpConditions(): Collection
    {
        return $this->jumpConditions;
    }

    public function addJumpCondition(JumpCondition $jumpCondition): self
    {
        if (!$this->jumpConditions->contains($jumpCondition)) {
            $this->jumpConditions->add($jumpCondition);
            $jumpCondition->setToQuestion($this);
        }

        return $this;
    }

    public function removeJumpCondition(JumpCondition $jumpCondition): self
    {
        if ($this->jumpConditions->removeElement($jumpCondition)) {
            // set the owning side to null (unless already changed)
            if ($jumpCondition->getToQuestion() === $this) {
                $jumpCondition->setToQuestion(null);
            }
        }

        return $this;
    }

    public function isIsRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function isCanGiveOwnAnswer(): ?bool
    {
        return $this->canGiveOwnAnswer;
    }

    public function setCanGiveOwnAnswer(bool $canGiveOwnAnswer): self
    {
        $this->canGiveOwnAnswer = $canGiveOwnAnswer;

        return $this;
    }

    public function getMaxVariants(): ?int
    {
        return $this->maxVariants;
    }

    public function setMaxVariants(?int $maxVariants): self
    {
        $this->maxVariants = $maxVariants;

        return $this;
    }
}
