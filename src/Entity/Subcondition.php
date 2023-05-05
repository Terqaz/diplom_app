<?php

namespace App\Entity;

use App\Repository\SubconditionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubconditionRepository::class)]
class Subcondition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Порядковый номер условия */
    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $serialNumber = null;

    /** Совпадает ли выбранный ответ на вопрос с answerVariant */
    #[ORM\Column(options: ['default' => true])]
    private ?bool $isEqual = true;

    #[ORM\ManyToOne(inversedBy: 'subconditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JumpCondition $jumpCondition = null;

    #[ORM\ManyToOne(inversedBy: 'subconditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnswerVariant $answerVariant = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJumpCondition(): ?JumpCondition
    {
        return $this->jumpCondition;
    }

    public function setJumpCondition(?JumpCondition $jumpCondition): self
    {
        $this->jumpCondition = $jumpCondition;

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

    public function isIsEqual(): ?bool
    {
        return $this->isEqual;
    }

    public function setIsEqual(bool $isEqual): self
    {
        $this->isEqual = $isEqual;

        return $this;
    }
}
