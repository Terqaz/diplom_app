<?php

namespace App\Entity;

use App\Enum\SocialNetworkCode;
use App\Repository\RespondentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RespondentRepository::class)]
class Respondent
{
    public const SOCIAL_NETWORK_ID_FIELD = [
        SocialNetworkCode::TELEGRAM => 'telegramId',
        SocialNetworkCode::VKONTAKTE => 'vkontakteId',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, unique: true)]
    private ?int $telegramId = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, unique: true)]
    private ?int $vkontakteId = null;

    #[ORM\Column(length: 128, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 16, nullable: true, unique: true)]
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'respondent', targetEntity: RespondentAnswer::class, cascade: ['persist'])]
    private Collection $answers;

    #[ORM\OneToMany(mappedBy: 'respondent', targetEntity: RespondentForm::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $forms;

    #[ORM\OneToMany(mappedBy: 'respondent', targetEntity: SurveyAccess::class)]
    private Collection $surveyAccesses;

    #[ORM\OneToMany(mappedBy: 'respondent', targetEntity: BotAccess::class)]
    private Collection $botAccesses;

    public function __construct()
    {
        $this->surveyAccesses = new ArrayCollection();
        $this->answers = new ArrayCollection();
        $this->forms = new ArrayCollection();
        $this->botAccesses = new ArrayCollection();
    }

    public function getSocialNetworkId(string $code): int|string
    {
        $fieldName = self::SOCIAL_NETWORK_ID_FIELD[$code];

        return $this->$fieldName;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelegramId(): ?int
    {
        return $this->telegramId;
    }

    public function setTelegramId(?int $telegramId): self
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    public function getVkontakteId(): ?int
    {
        return $this->vkontakteId;
    }

    public function setVkontakteId(?int $vkontakteId): self
    {
        $this->vkontakteId = $vkontakteId;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, RespondentAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if (!$this->answers->contains($respondentAnswer)) {
            $this->answers->add($respondentAnswer);
            $respondentAnswer->setRespondent($this);
        }

        return $this;
    }

    public function removeRespondentAnswer(RespondentAnswer $respondentAnswer): self
    {
        if ($this->answers->removeElement($respondentAnswer)) {
            // set the owning side to null (unless already changed)
            if ($respondentAnswer->getRespondent() === $this) {
                $respondentAnswer->setRespondent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RespondentForm>
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function addRespondentForm(RespondentForm $respondentForm): self
    {
        if (!$this->forms->contains($respondentForm)) {
            $this->forms->add($respondentForm);
            $respondentForm->setRespondent($this);
        }

        return $this;
    }

    public function removeRespondentForm(RespondentForm $respondentForm): self
    {
        if ($this->forms->removeElement($respondentForm)) {
            // set the owning side to null (unless already changed)
            if ($respondentForm->getRespondent() === $this) {
                $respondentForm->setRespondent(null);
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
            $surveyAccess->setRespondent($this);
        }

        return $this;
    }

    public function removeSurveyAccess(SurveyAccess $surveyAccess): self
    {
        if ($this->surveyAccesses->removeElement($surveyAccess)) {
            // set the owning side to null (unless already changed)
            if ($surveyAccess->getRespondent() === $this) {
                $surveyAccess->setRespondent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BotAccess>
     */
    public function getBotAccesses(): Collection
    {
        return $this->botAccesses;
    }

    public function addBotAccess(BotAccess $botAccess): self
    {
        if (!$this->botAccesses->contains($botAccess)) {
            $this->botAccesses->add($botAccess);
            $botAccess->setRespondent($this);
        }

        return $this;
    }

    public function removeBotAccess(BotAccess $botAccess): self
    {
        if ($this->botAccesses->removeElement($botAccess)) {
            // set the owning side to null (unless already changed)
            if ($botAccess->getRespondent() === $this) {
                $botAccess->setRespondent(null);
            }
        }

        return $this;
    }
}
