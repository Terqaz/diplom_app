<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isOnce = null;

    #[ORM\Column(length: 32)]
    private ?string $type = null;

    #[ORM\Column(length: 128)]
    private ?string $repeatValues = null;

    #[ORM\Column]
    private ?bool $isNoticeBeforeStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastRepeat = null;

    #[ORM\OneToOne(inversedBy: 'schedule', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRepeatValues(): ?string
    {
        return $this->repeatValues;
    }

    public function setRepeatValues(string $repeatValues): self
    {
        $this->repeatValues = $repeatValues;

        return $this;
    }

    public function isIsNoticeBeforeStart(): ?bool
    {
        return $this->isNoticeBeforeStart;
    }

    public function setIsNoticeBeforeStart(bool $isNoticeBeforeStart): self
    {
        $this->isNoticeBeforeStart = $isNoticeBeforeStart;

        return $this;
    }

    public function getLastRepeat(): ?\DateTimeInterface
    {
        return $this->lastRepeat;
    }

    public function setLastRepeat(\DateTimeInterface $lastRepeat): self
    {
        $this->lastRepeat = $lastRepeat;

        return $this;
    }

    public function isIsOnce(): ?bool
    {
        return $this->isOnce;
    }

    public function setIsOnce(bool $isOnce): self
    {
        $this->isOnce = $isOnce;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }
}
