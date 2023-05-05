<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user_data')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['phone'], message: 'There is already an account with this phone')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $firstName = null;

    #[ORM\Column(length: 64)]
    private ?string $lastName = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $patronymic = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['userAccessesEdit'])]
    protected ?string $email = null;

    #[ORM\Column(length: 16, unique: true, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'userData', targetEntity: BotUser::class)]
    private Collection $botUsers;

    #[ORM\OneToMany(mappedBy: 'userData', targetEntity: SurveyUser::class)]
    private Collection $surveyUsers;

    public function __construct()
    {
        $this->botUsers = new ArrayCollection();
        $this->surveyUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /** @return string ФИО сотрудника */
    public function getFullName(): string
    {
        $s = $this->lastName . ' ' . $this->firstName;
        if (null !== $this->patronymic && '' !== $this->patronymic) {
            $s .= ' ' . $this->patronymic;
        }
        return $s;
    }

    #[Groups(['userAccessesEdit'])]
    public function getLastAndFirstName(): string
    {
        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(string $patronymic): self
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function addAccess(BotUser|SurveyUser $entity): self
    {
        if ($entity instanceof BotUser) {
            $this->addBotUser($entity);
        } else if ($entity instanceof SurveyUser) {
            $this->addSurveyUser($entity);
        }

        return $this;
    }

    public function removeAccess(BotUser|SurveyUser $entity): self
    {
        if ($entity instanceof BotUser) {
            $this->removeBotUser($entity);
        } else if ($entity instanceof SurveyUser) {
            $this->removeSurveyUser($entity);
        }

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
            $botUser->setUserData($this);
        }

        return $this;
    }

    public function removeBotUser(BotUser $botUser): self
    {
        if ($this->botUsers->removeElement($botUser)) {
            // set the owning side to null (unless already changed)
            if ($botUser->getUserData() === $this) {
                $botUser->setUserData(null);
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
            $surveyUser->setUserData($this);
        }

        return $this;
    }

    public function removeSurveyUser(SurveyUser $surveyUser): self
    {
        if ($this->surveyUsers->removeElement($surveyUser)) {
            // set the owning side to null (unless already changed)
            if ($surveyUser->getUserData() === $this) {
                $surveyUser->setUserData(null);
            }
        }

        return $this;
    }
}
