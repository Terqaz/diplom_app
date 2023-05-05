<?php

namespace App\Entity;

use App\Enum\AccessProperty;
use App\Enum\SocialNetworkCode;
use App\Enum\UserRole;
use App\Repository\BotRepository;
use App\Utils\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BotRepository::class)]
class Bot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['accessesEdit'])]
    private ?int $id = null;

    private const INPUT_CORRECT_VALUE = 'Введите корректное значение';

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: Bot::INPUT_CORRECT_VALUE,
        maxMessage: Bot::INPUT_CORRECT_VALUE,
    )]
    #[Groups(['accessesEdit'])]
    private ?string $title = null;

    #[ORM\Column(length: 1024, nullable: true)]
    #[Assert\Length(
        min: 16,
        max: 1024,
        minMessage: Bot::INPUT_CORRECT_VALUE,
        maxMessage: Bot::INPUT_CORRECT_VALUE,
    )]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isPrivate = true;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: BotUser::class, cascade: ['persist'])]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: Survey::class, cascade: ['persist'])]
    #[OrderBy(['id' => 'ASC'])]
    private Collection $surveys;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: SocialNetworkConfig::class, cascade: ['persist'])]
    private Collection $socialNetworkConfigs;

    #[ORM\OneToMany(mappedBy: 'bot', targetEntity: BotAccess::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $respondentAccesses;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->surveys = new ArrayCollection();
        $this->socialNetworkConfigs = new ArrayCollection();
        $this->respondentAccesses = new ArrayCollection();
    }

    public function getAdmin(): User
    {
        return $this->getUsersByRole(UserRole::ADMIN)->get(0);
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
            ->map(fn (BotUser $botUser): User => $botUser->getUserData());
    }

    /** Получить роль пользователя по отношению к боту */
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

    public function getRespondentAccessBy(string $property, string $value): ?BotAccess
    {
        $access = $this->respondentAccesses->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('propertyName', $property))
                ->andWhere(Criteria::expr()->eq('propertyValue', $value))
        )->first();

        return $access ? $access : null;
    }

    public function checkRespondentAccess(Respondent $respondent): bool
    {
        if (!$this->isPrivate) {
            return true;
        }

        /** @var ArrayCollection $accesses */
        $accesses = $this->respondentAccesses;

        $criteria = Criteria::create();

        foreach (AccessProperty::TYPES as $property) {
            $getter = 'get' . StringUtils::capitalize($property);
            $criteria->orWhere(Criteria::expr()->andX(
                Criteria::expr()->eq('propertyName', $property),
                Criteria::expr()->eq('propertyValue', $respondent->$getter())
            ));
        }

        return 0 < $accesses->matching($criteria)->count();
    }

    public function isUsedByRespondent(Respondent $respondent): bool
    {
        return 0 < $this->respondentAccesses->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('respondent', $respondent))
        )->count();
    }

    public function getTelegramConfig(): ?SocialNetworkConfig
    {
        return $this->getSocialNetworkConfigByCode(SocialNetworkCode::TELEGRAM);
    }

    public function getVkontakteConfig(): ?SocialNetworkConfig
    {
        return $this->getSocialNetworkConfigByCode(SocialNetworkCode::VKONTAKTE);
    }

    public function getSocialNetworkConfigByCode(string $code): ?SocialNetworkConfig
    {
        /** @var ArrayCollection */
        $networks = $this->socialNetworkConfigs;

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
    #[Groups(['userAccessesEdit'])]
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(BotUser $botUser): self
    {
        if (!$this->users->contains($botUser)) {
            $this->users->add($botUser);
            $botUser->setBot($this);
        }

        return $this;
    }

    public function removeUser(BotUser $botUser): self
    {
        if ($this->users->removeElement($botUser)) {
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

    public function getEnabledSurveys(): Collection
    {
        return $this->surveys->matching(
            Criteria::create()->where(Criteria::expr()->eq('isEnabled', true))
        );
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
     * @return Collection<int, SocialNetworkConfig>
     */
    public function getSocialNetworkConfigs(): Collection
    {
        return $this->socialNetworkConfigs;
    }

    public function addSocialNetworkConfig(SocialNetworkConfig $socialNetworkConfig): self
    {
        if (!$this->socialNetworkConfigs->contains($socialNetworkConfig)) {
            $this->socialNetworkConfigs->add($socialNetworkConfig);
            $socialNetworkConfig->setBot($this);
        }

        return $this;
    }

    public function removeSocialNetworkConfig(SocialNetworkConfig $socialNetworkConfig): self
    {
        if ($this->socialNetworkConfigs->removeElement($socialNetworkConfig)) {
            // set the owning side to null (unless already changed)
            if ($socialNetworkConfig->getBot() === $this) {
                $socialNetworkConfig->setBot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BotAccess>
     */
    public function getRespondentAccesses(): Collection
    {
        return $this->respondentAccesses;
    }

    public function addRespondentAccess(BotAccess $botAccess): self
    {
        if (!$this->respondentAccesses->contains($botAccess)) {
            $this->respondentAccesses->add($botAccess);
            $botAccess->setBot($this);
        }

        return $this;
    }

    public function removeRespondentAccess(BotAccess $botAccess): self
    {
        if ($this->respondentAccesses->removeElement($botAccess)) {
            // set the owning side to null (unless already changed)
            if ($botAccess->getBot() === $this) {
                $botAccess->setBot(null);
            }
        }

        return $this;
    }
}
