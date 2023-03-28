<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
class Survey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isTest = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isShuffleVariants = false;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isPrivate = true;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isPhoneRequired = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEmailRequired = false;

    #[ORM\ManyToOne(inversedBy: 'surveys')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bot $bot = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyUser::class)]
    private Collection $surveyUsers;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Question::class)]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: JumpCondition::class, orphanRemoval: true)]
    private Collection $jumpConditions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyIteration::class, orphanRemoval: true)]
    private Collection $surveyIterations;

    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'])]
    private ?Schedule $schedule = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: RespondentAttempt::class, orphanRemoval: true)]
    private Collection $respondentAttempts;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyAccess::class, orphanRemoval: true)]
    private Collection $surveyAccesses;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->surveyUsers = new ArrayCollection();
        $this->jumpConditions = new ArrayCollection();
        $this->surveyIterations = new ArrayCollection();
        $this->respondentAttempts = new ArrayCollection();
        $this->surveyAccesses = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getBot(): ?Bot
    {
        return $this->bot;
    }

    public function setBot(?Bot $bot): self
    {
        $this->bot = $bot;

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(Schedule $schedule): self
    {
        // set the owning side of the relation if necessary
        if ($schedule->getSurvey() !== $this) {
            $schedule->setSurvey($this);
        }

        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setSurvey($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSurvey() === $this) {
                $question->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyUser>
     */
    public function getSurveyUsers(): Collection
    {
        return $this->surveyUsers;
    }

    public function addSurveyUser(SurveyUser $surveyUser): self
    {
        if (!$this->surveyUsers->contains($surveyUser)) {
            $this->surveyUsers->add($surveyUser);
            $surveyUser->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyUser(SurveyUser $surveyUser): self
    {
        if ($this->surveyUsers->removeElement($surveyUser)) {
            // set the owning side to null (unless already changed)
            if ($surveyUser->getSurvey() === $this) {
                $surveyUser->setSurvey(null);
            }
        }

        return $this;
    }

    public function isIsTest(): ?bool
    {
        return $this->isTest;
    }

    public function setIsTest(bool $isTest): self
    {
        $this->isTest = $isTest;

        return $this;
    }

    public function isIsShuffleVariants(): ?bool
    {
        return $this->isShuffleVariants;
    }

    public function setIsShuffleVariants(bool $isShuffleVariants): self
    {
        $this->isShuffleVariants = $isShuffleVariants;

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
            $jumpCondition->setSurvey($this);
        }

        return $this;
    }

    public function removeJumpCondition(JumpCondition $jumpCondition): self
    {
        if ($this->jumpConditions->removeElement($jumpCondition)) {
            // set the owning side to null (unless already changed)
            if ($jumpCondition->getSurvey() === $this) {
                $jumpCondition->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyIteration>
     */
    public function getSurveyIterations(): Collection
    {
        return $this->surveyIterations;
    }

    public function addSurveyIteration(SurveyIteration $surveyIteration): self
    {
        if (!$this->surveyIterations->contains($surveyIteration)) {
            $this->surveyIterations->add($surveyIteration);
            $surveyIteration->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyIteration(SurveyIteration $surveyIteration): self
    {
        if ($this->surveyIterations->removeElement($surveyIteration)) {
            // set the owning side to null (unless already changed)
            if ($surveyIteration->getSurvey() === $this) {
                $surveyIteration->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RespondentAttempt>
     */
    public function getRespondentAttempts(): Collection
    {
        return $this->respondentAttempts;
    }

    public function addRespondentAttempt(RespondentAttempt $respondentAttempt): self
    {
        if (!$this->respondentAttempts->contains($respondentAttempt)) {
            $this->respondentAttempts->add($respondentAttempt);
            $respondentAttempt->setSurvey($this);
        }

        return $this;
    }

    public function removeRespondentAttempt(RespondentAttempt $respondentAttempt): self
    {
        if ($this->respondentAttempts->removeElement($respondentAttempt)) {
            // set the owning side to null (unless already changed)
            if ($respondentAttempt->getSurvey() === $this) {
                $respondentAttempt->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyAccess>
     */
    public function getSurveyAccesses(): Collection
    {
        return $this->surveyAccesses;
    }

    public function addSurveyAccess(SurveyAccess $surveyAccess): self
    {
        if (!$this->surveyAccesses->contains($surveyAccess)) {
            $this->surveyAccesses->add($surveyAccess);
            $surveyAccess->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyAccess(SurveyAccess $surveyAccess): self
    {
        if ($this->surveyAccesses->removeElement($surveyAccess)) {
            // set the owning side to null (unless already changed)
            if ($surveyAccess->getSurvey() === $this) {
                $surveyAccess->setSurvey(null);
            }
        }

        return $this;
    }

    public function isIsPhoneRequired(): ?bool
    {
        return $this->isPhoneRequired;
    }

    public function setIsPhoneRequired(bool $isPhoneRequired): self
    {
        $this->isPhoneRequired = $isPhoneRequired;

        return $this;
    }

    public function isIsEmailRequired(): ?bool
    {
        return $this->isEmailRequired;
    }

    public function setIsEmailRequired(bool $isEmailRequired): self
    {
        $this->isEmailRequired = $isEmailRequired;

        return $this;
    }
}
