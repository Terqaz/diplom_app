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

    #[ORM\Column]
    private ?bool $isPrivate = null;

    #[ORM\ManyToOne(inversedBy: 'surveys')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Bot $bot = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: Question::class)]
    private Collection $questions;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: RespondentAccess::class)]
    private Collection $respondentAccesses;

    #[ORM\OneToOne(mappedBy: 'survey', cascade: ['persist', 'remove'])]
    private ?Schedule $schedule = null;

    #[ORM\OneToMany(mappedBy: 'survey', targetEntity: SurveyUser::class)]
    private Collection $surveyUsers;

    public function __construct()
    {
        $this->respondentAccesses = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->surveyUsers = new ArrayCollection();
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

    /**
     * @return Collection<int, RespondentAccess>
     */
    public function getRespondentAccesses(): Collection
    {
        return $this->respondentAccesses;
    }

    public function addRespondentAccess(RespondentAccess $respondentAccess): self
    {
        if (!$this->respondentAccesses->contains($respondentAccess)) {
            $this->respondentAccesses->add($respondentAccess);
            $respondentAccess->setSurvey($this);
        }

        return $this;
    }

    public function removeRespondentAccess(RespondentAccess $respondentAccess): self
    {
        if ($this->respondentAccesses->removeElement($respondentAccess)) {
            // set the owning side to null (unless already changed)
            if ($respondentAccess->getSurvey() === $this) {
                $respondentAccess->setSurvey(null);
            }
        }

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
}
