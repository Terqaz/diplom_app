<?php

namespace App\Entity;

use App\Enum\AccessProperty;
use App\Repository\BotAccessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BotAccessRepository::class)]
class BotAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Assert\AtLeastOneOf([
        new Assert\IsNull(),
        new Assert\Choice(choices: AccessProperty::TYPES),
    ])]
    private ?string $propertyName = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $propertyValue = null;

    #[ORM\ManyToOne(inversedBy: 'botAccesses')]
    private ?Respondent $respondent = null;

    #[ORM\ManyToOne(inversedBy: 'respondentAccesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bot $bot = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;

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

    public function getRespondent(): ?Respondent
    {
        return $this->respondent;
    }

    public function setRespondent(?Respondent $respondent): self
    {
        $this->respondent = $respondent;

        return $this;
    }

    public function getPropertyValue(): ?string
    {
        return $this->propertyValue;
    }

    public function setPropertyValue(string $propertyValue): self
    {
        $this->propertyValue = $propertyValue;

        return $this;
    }
}
