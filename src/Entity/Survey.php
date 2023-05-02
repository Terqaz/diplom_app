<?php

namespace App\Entity;

use App\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isPrivate = true;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEnabled = false;

    /** Можно ли проходить несколько раз */
    #[ORM\Column(options: ['default' => false])]
    private ?bool $isMultiple = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isPhoneRequired = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEmailRequired = false;

    #[ORM\ManyToOne(inversedBy: 'surveys')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bot $bot = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyUser::class, cascade: ['persist'])]
    private Collection $surveyUsers;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Question::class, cascade: ['persist'])]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: JumpCondition::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $jumpConditions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyIteration::class, orphanRemoval: true)]
    private Collection $surveyIterations;

    /** Не имеет смысл, если isMultiple=true */
    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'])]
    private ?Schedule $schedule = null;

    /** Не имеет смысл, если isMultiple=false и schedule=null*/
    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: RespondentForm::class, orphanRemoval: true)]
    private Collection $respondentForms;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyAccess::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $respondentAccesses;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->surveyUsers = new ArrayCollection();
        $this->jumpConditions = new ArrayCollection();
        $this->surveyIterations = new ArrayCollection();
        $this->respondentForms = new ArrayCollection();
        $this->respondentAccesses = new ArrayCollection();
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

    public function isPrivate(): ?bool
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
     * @return Collection<int, RespondentForm>
     */
    public function getRespondentForms(): Collection
    {
        return $this->respondentForms;
    }

    public function addRespondentForm(RespondentForm $respondentForm): self
    {
        if (!$this->respondentForms->contains($respondentForm)) {
            $this->respondentForms->add($respondentForm);
            $respondentForm->setSurvey($this);
        }

        return $this;
    }

    public function removeRespondentForm(RespondentForm $respondentForm): self
    {
        if ($this->respondentForms->removeElement($respondentForm)) {
            // set the owning side to null (unless already changed)
            if ($respondentForm->getSurvey() === $this) {
                $respondentForm->setSurvey(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SurveyAccess>
     */
    public function getRespondentAccesses(): Collection
    {
        return $this->respondentAccesses;
    }

    public function addSurveyAccess(SurveyAccess $surveyAccess): self
    {
        if (!$this->respondentAccesses->contains($surveyAccess)) {
            $this->respondentAccesses->add($surveyAccess);
            $surveyAccess->setSurvey($this);
        }

        return $this;
    }

    public function removeSurveyAccess(SurveyAccess $surveyAccess): self
    {
        if ($this->respondentAccesses->removeElement($surveyAccess)) {
            // set the owning side to null (unless already changed)
            if ($surveyAccess->getSurvey() === $this) {
                $surveyAccess->setSurvey(null);
            }
        }

        return $this;
    }

    public function isPhoneRequired(): ?bool
    {
        return $this->isPhoneRequired;
    }

    public function setIsPhoneRequired(bool $isPhoneRequired): self
    {
        $this->isPhoneRequired = $isPhoneRequired;

        return $this;
    }

    public function isEmailRequired(): ?bool
    {
        return $this->isEmailRequired;
    }

    public function setIsEmailRequired(bool $isEmailRequired): self
    {
        $this->isEmailRequired = $isEmailRequired;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    // todo
    /** Если переданный isEnabled = false и есть RespondentForm, у которой sendDate !== null, то опрос нельзя просто так редактировать */
    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isMultiple(): ?bool
    {
        return $this->isMultiple;
    }

    public function setIsMultiple(bool $isMultiple): self
    {
        $this->isMultiple = $isMultiple;

        return $this;
    }
}
