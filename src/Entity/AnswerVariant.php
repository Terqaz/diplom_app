<?php

namespace App\Entity;

use App\Repository\AnswerVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AnswerVariantRepository::class)]
class AnswerVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $serialNumber = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\OneToMany(mappedBy: 'answerVariant', targetEntity: RespondentAnswer::class)]
    private Collection $respondentAnswer;

    #[ORM\OneToMany(mappedBy: 'answerVariant', targetEntity: Subcondition::class, orphanRemoval: true)]
    private Collection $subconditions;

    public function __construct()
    {
        $this->respondentAnswer = new ArrayCollection();
        $this->subconditions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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

    /**
     * @return Collection<int, RespondentAnswer>
     */
    public function getRespondentAnswers(): Collection
    {
        return $this->respondentAnswer;
    }

    public function addRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if (!$this->respondentAnswer->contains($respondentAnswer)) {
            $this->respondentAnswer->add($respondentAnswer);
            $respondentAnswer->setAnswerVariant($this);
        }

        return $this;
    }

    public function removeRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if ($this->respondentAnswer->removeElement($respondentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($respondentAnswer->getAnswerVariant() === $this) {
                $respondentAnswer->setAnswerVariant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subcondition>
     */
    public function getSubconditions(): Collection
    {
        return $this->subconditions;
    }

    public function addSubcondition(Subcondition $subcondition): self
    {
        if (!$this->subconditions->contains($subcondition)) {
            $this->subconditions->add($subcondition);
            $subcondition->setAnswerVariant($this);
        }

        return $this;
    }

    public function removeSubcondition(Subcondition $subcondition): self
    {
        if ($this->subconditions->removeElement($subcondition)) {
            // set the owning side to null (unless already changed)
            if ($subcondition->getAnswerVariant() === $this) {
                $subcondition->setAnswerVariant(null);
            }
        }

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
