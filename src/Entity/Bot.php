<?php

namespace App\Entity;

use App\Repository\BotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BotRepository::class)]
class Bot
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

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: BotUser::class, cascade: ['persist'])]
    private Collection $botUsers;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: Survey::class, cascade: ['persist'])]
    private Collection $surveys;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: SocialNetwork::class, cascade: ['persist'])]
    private Collection $socialNetworks;

    public function __construct()
    {
        $this->botUsers = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->socialNetworks = new ArrayCollection();
    }

    public function getTelegramNetwork(): SocialNetwork
    {
        return $this->getSocialNetwork(SocialNetwork::TELEGRAM_CODE);
    }

    public function getVkontakteNetwork(): SocialNetwork
    {
        return $this->getSocialNetwork(SocialNetwork::VKONTAKTE_CODE);
    }

    public function getSocialNetwork(string $code): SocialNetwork
    {
        /** @var ArrayCollection */
        $networks = $this->socialNetworks;

        return $networks->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('code', $code))
                ->setMaxResults(1)
        )->get(0);
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

    /**
     * @return Collection<int, BotUser>
     */
    public function getBotUsers(): Collection
    {
        return $this->botUsers;
    }

    public function addBotUser(BotUser $botUser): self
    {
        if (!$this->botUsers->contains($botUser)) {
            $this->botUsers->add($botUser);
            $botUser->setBot($this);
        }

        return $this;
    }

    public function removeBotUser(BotUser $botUser): self
    {
        if ($this->botUsers->removeElement($botUser)) {
            // set the owning side to null (unless already changed)
            if ($botUser->getBot() === $this) {
                $botUser->setBot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Survey>
     */
    public function getSurveys(): Collection
    {
        return $this->surveys;
    }

    public function addSurvey(Survey $survey): self
    {
        if (!$this->surveys->contains($survey)) {
            $this->surveys->add($survey);
            $survey->setBot($this);
        }

        return $this;
    }

    public function removeSurvey(Survey $survey): self
    {
        if ($this->surveys->removeElement($survey)) {
            // set the owning side to null (unless already changed)
            if ($survey->getBot() === $this) {
                $survey->setBot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SocialNetwork>
     */
    public function getSocialNetworks(): Collection
    {
        return $this->socialNetworks;
    }

    public function addSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if (!$this->socialNetworks->contains($socialNetwork)) {
            $this->socialNetworks->add($socialNetwork);
            $socialNetwork->setBot($this);
        }

        return $this;
    }

    public function removeSocialNetwork(SocialNetwork $socialNetwork): self
    {
        if ($this->socialNetworks->removeElement($socialNetwork)) {
            // set the owning side to null (unless already changed)
            if ($socialNetwork->getBot() === $this) {
                $socialNetwork->setBot(null);
            }
        }

        return $this;
    }
}
