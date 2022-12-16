<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 400, unique: true)]
    private ?string $title = null;

    #[ORM\Column(length: 32)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: AnswerVariant::class)]
    private Collection $answers;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $correctAnswer = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: RespondentAnswer::class)]
    private Collection $respondentAnswers;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->respondentAnswers = new ArrayCollection();
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, AnswerVariant>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(AnswerVariant $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(AnswerVariant $answer): self
    {
        if ($this->answers->removeElement($answer)) {
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
}
