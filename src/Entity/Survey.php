<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
class Survey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['surveyFormEdit', 'accessesEdit', 'surveyScheduleEdit'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['surveyFormEdit', 'accessesEdit', 'surveyScheduleEdit'])]
    private ?string $title = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isPrivate = true;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isEnabled = false;

    /** Предназначен ли для многоразового прохождения */
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['surveyScheduleEdit'])]
    private ?bool $isMultiple = false;

    /** Обязательно ли респонденту вводить номер телефона перед опросом */
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['surveyFormEdit'])]
    private ?bool $isPhoneRequired = false;

    /** Обязательно ли респонденту вводить email перед опросом */
    #[ORM\Column(options: ['default' => false])]
    #[Groups(['surveyFormEdit'])]
    private ?bool $isEmailRequired = false;

    #[ORM\ManyToOne(inversedBy: 'surveys')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bot $bot = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyUser::class, cascade: ['persist'])]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Question::class, cascade: ['persist'])]
    #[OrderBy(['serialNumber' => 'ASC'])]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: JumpCondition::class, orphanRemoval: true, cascade: ['persist'])]
    #[OrderBy(['serialNumber' => 'ASC'])]
    private Collection $jumpConditions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyIteration::class, orphanRemoval: true)]
    #[OrderBy(['startDate' => 'ASC'])]
    private Collection $surveyIterations;

    /** Не имеет смысл, если isMultiple=true */
    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'])]
    #[Groups(['surveyScheduleEdit'])]
    private ?Schedule $schedule = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: RespondentForm::class, orphanRemoval: true)]
    private Collection $respondentForms;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyAccess::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $respondentAccesses;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->jumpConditions = new ArrayCollection();
        $this->surveyIterations = new ArrayCollection();
        $this->respondentForms = new ArrayCollection();
        $this->respondentAccesses = new ArrayCollection();
    }

    /**
     * @param string $role
     * @return Collection
     */
    public function getUsersByRole(string $role): Collection
    {
        /** @var ArrayCollection $accesses */
        $accesses = $this->users;

        return $accesses->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('role', $role))
        )
            ->map(fn (SurveyUser $surveyUser): User => $surveyUser->getUserData());
    }

    /** Получить роль пользователя по отношению к опросу */
    public function getUserRole(?User $user): string
    {
        if (null === $user) {
            return UserRole::ANONYM;
        }

        /** @var ArrayCollection $accesses */
        $accesses = $this->users;

        $role = $accesses->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('userData', $user))
        )
            ->get(0)
            ?->getRole();

        if (null === $role) {
            return UserRole::AUTHORIZED;
        }

        return $role;
    }

    public function getQuestionByNumber(int $formNumber): ?Question
    {
        $question = $this->questions->matching(
            Criteria::create()->where(Criteria::expr()->eq('serialNumber', $formNumber))
        )->first();

        return $question ? $question : null;
    }

    public function getJumpConditionByNumber(int $formNumber): ?JumpCondition
    {
        $jump = $this->jumpConditions->matching(
            Criteria::create()->where(Criteria::expr()->eq('serialNumber', $formNumber))
        )->first();

        return $jump ? $jump : null;
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

    public function setSchedule(?Schedule $schedule): self
    {
        // set the owning side of the relation if necessary
        if ($schedule !== null && $schedule->getSurvey() !== $this) {
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
    #[Groups(['userAccessesEdit'])]
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(SurveyUser $surveyUser): self
    {
        if (!$this->users->contains($surveyUser)) {
            $this->users->add($surveyUser);
            $surveyUser->setSurvey($this);
        }

        return $this;
    }

    public function removeUser(SurveyUser $surveyUser): self
    {
        if ($this->users->removeElement($surveyUser)) {
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

    public function getRespondentAccessBy(string $property, string $value): ?SurveyAccess
    {
        $access = $this->respondentAccesses->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('propertyName', $property))
                ->andWhere(Criteria::expr()->eq('propertyValue', $value))
        )->first();

        return $access ? $access : null;
    }

    public function addRespondentAccess(SurveyAccess $surveyAccess): self
    {
        if (!$this->respondentAccesses->contains($surveyAccess)) {
            $this->respondentAccesses->add($surveyAccess);
            $surveyAccess->setSurvey($this);
        }

        return $this;
    }

    public function removeRespondentAccess(SurveyAccess $surveyAccess): self
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
