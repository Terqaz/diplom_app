<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    /** В течение дня */
    public const DURING_DAY = 'during_day';
    /** В течение недели */
    public const DURING_WEEK = 'during_week';
    /** В течение месяца */
    public const DURING_MONTH = 'during_month';
    /** В течение года */
    public const DURING_YEAR = 'during_year';

    public const TYPES = [
        self::DURING_DAY,
        self::DURING_WEEK,
        self::DURING_MONTH,
        self::DURING_YEAR,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Тип повторения */
    #[ORM\Column(length: 32)]
    #[Assert\Choice(choices: Schedule::TYPES)]
    private ?string $type = null;

    /**
     * Даты и времени для повтора.
     * Если type в течение дня, то список "часы:минуты", например: ['09:00', '13:40'].
     * Если type в течение недели или месяца, то список дней и "часы:минуты", например: [[1, 4, 5], '13:40'].
     * Если type в течение года, то список номеров месяцев, день и "часы:минуты", например: [[1, 4, 5], 15, '13:40'].
     */
    #[ORM\Column(length: 128, type: Types::JSON)]
    private ?string $repeatValues = null;

    /** Дата следующего повторения */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $nextRepeat = null;

    /** Провести только раз во время в nextRepeat */
    #[ORM\Column(options: ['default' => true])]
    private ?bool $isOnce = true;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $isNoticeOnStart = false;

    /** Дополнительно оповестить за указанное число минут */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $noticeBefore = null;

    #[ORM\OneToOne(inversedBy: 'schedule', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Survey $survey = null;


    public function setTypeAndRepeatValues(string $type, string $repeatValues): self
    {
        $this->type = $type;
        $this->repeatValues = $repeatValues;

        $this->updateNextRepeat();

        return $this;
    }

    private function updateNextRepeat(): void
    {
        // TODO
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    // public function setType(string $type): self
    // {
    //     $this->type = $type;

    //     return $this;
    // }

    public function getRepeatValues(): ?string
    {
        return $this->repeatValues;
    }

    public function setRepeatValues(string $repeatValues): self
    {
        $this->repeatValues = $repeatValues;

        $this->updateNextRepeat();

        return $this;
    }

    public function getNextRepeat(): ?\DateTimeInterface
    {
        return $this->nextRepeat;
    }

    public function setNextRepeat(\DateTimeInterface $nextRepeat): self
    {
        $this->nextRepeat = $nextRepeat;

        return $this;
    }

    public function isOnce(): ?bool
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

    public function getNoticeBefore(): ?string
    {
        return $this->noticeBefore;
    }

    public function setNoticeBefore(?string $noticeBefore): self
    {
        $this->noticeBefore = $noticeBefore;

        return $this;
    }

    public function isNoticeOnStart(): ?bool
    {
        return $this->isNoticeOnStart;
    }

    public function setIsNoticeOnStart(bool $isNoticeOnStart): self
    {
        $this->isNoticeOnStart = $isNoticeOnStart;

        return $this;
    }
}
