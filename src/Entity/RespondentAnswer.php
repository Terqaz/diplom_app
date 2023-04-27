<?php

namespace App\Entity;

use App\Repository\RespondentAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentAnswerRepository::class)]
class RespondentAnswer
{
    public const FIRST_SERIAL_NUMBER = 0;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    /** Для указания порядка ответов */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $serialNumber = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Respondent $respondent = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAnswer')]
    private ?AnswerVariant $answerVariant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getRespondent(): ?Respondent
    {
        return $this->respondent;
    }

    public function setRespondent(?Respondent $respondent): self
    {
        $this->respondent = $respondent;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswerVariant(): ?AnswerVariant
    {
        return $this->answerVariant;
    }

    public function setAnswerVariant(?AnswerVariant $answerVariant): self
    {
        $this->answerVariant = $answerVariant;

        return $this;
    }

    public function getSerialNumber(): ?int
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?int $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }
}
