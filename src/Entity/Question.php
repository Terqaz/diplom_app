<?php

namespace App\Entity;

use App\Enum\AnswerValueType;
use App\Enum\QuestionType;
use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: QuestionType::TYPES)]
    #[Groups(['surveyFormEdit'])]
    private ?string $type = null;

    /** Порядковый номер среди всех ответов и условий перехода */
    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['surveyFormEdit'])]
    private ?int $serialNumber = null;

    #[ORM\Column(length: 400)]
    #[Groups(['surveyFormEdit'])]
    private ?string $title = null;

    #[ORM\Column(length: 32, options: ['default' => AnswerValueType::STRING])]
    #[Assert\Choice(choices: AnswerValueType::TYPES)]
    #[Groups(['surveyFormEdit'])]
    private ?string $answerValueType = AnswerValueType::STRING;

    /**
     * Если через запятую, то границы интервалов шкалы.
     * Если 2 числа через тире, то границы вводимого числа.
     * Границы всегда включаются в интервал
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['surveyFormEdit'])]
    private ?string $intervalBorders = null;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['surveyFormEdit'])]
    private ?bool $isRequired = true;

    /** Максимальное число собственных ответов пользователя */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    #[Groups(['surveyFormEdit'])]
    private ?int $ownAnswersCount = 0;

    /** Максимальное число выбираемых ответов */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0])]
    #[Groups(['surveyFormEdit'])]
    private ?int $maxVariants = 0;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: AnswerVariant::class, cascade: ['persist', 'remove'])]
    #[OrderBy(['serialNumber' => 'ASC'])]
    private Collection $variants;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: RespondentAnswer::class, cascade: ['persist', 'remove'])]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: 'toQuestion', targetEntity: JumpCondition::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $jumpConditions;

    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->jumpConditions = new ArrayCollection();
    }

    /**
     * @return int[]|null
     */
    public function getNumberVariantsBorders(): ?array
    {
        $borders = explode('-', $this->intervalBorders);
        
        if (count($borders) !== 2) {
            return null;
        }

        return [(int)$borders[0], (int)$borders[1]];
    }

    public function getVariantByNumber(int $number): ?AnswerVariant
    {
        $variant = $this->variants->matching(
            Criteria::create()->where(Criteria::expr()->eq('serialNumber', $number))
        )->first();

        return $variant ? $variant : null;
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

    /**
     * @return Collection<int, RespondentAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if (!$this->answers->contains($respondentAnswer)) {
            $this->answers->add($respondentAnswer);
            $respondentAnswer->setQuestion($this);
        }

        return $this;
    }

    public function removeRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if ($this->answers->removeElement($respondentAnswer)) {
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

    public function isRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOwnAnswersCount(): int
    {
        return $this->ownAnswersCount;
    }

    public function setOwnAnswersCount(int $ownAnswersCount): self
    {
        $this->ownAnswersCount = $ownAnswersCount;

        return $this;
    }

    public function getAnswerValueType(): ?string
    {
        return $this->answerValueType;
    }

    public function setAnswerValueType(string $answerValueType): self
    {
        $this->answerValueType = $answerValueType;

        return $this;
    }
}
