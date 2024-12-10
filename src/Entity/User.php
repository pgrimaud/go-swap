<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPokemon::class, orphanRemoval: true)]
    private Collection $userPokemon;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserPvPPokemon::class, orphanRemoval: true)]
    private Collection $userPvPPokemon;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PvPQuiz>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PvPQuiz::class, orphanRemoval: true)]
    private Collection $pvpQuizzes;

    public function __construct()
    {
        $this->userPokemon = new ArrayCollection();
        $this->userPvPPokemon = new ArrayCollection();
        $this->pvpQuizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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
    public function getPassword(): ?string
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
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, UserPokemon>
     */
    public function getUserPokemon(): Collection
    {
        return $this->userPokemon;
    }

    public function addUserPokemon(UserPokemon $userPokemon): self
    {
        if (!$this->userPokemon->contains($userPokemon)) {
            $this->userPokemon->add($userPokemon);
            $userPokemon->setUser($this);
        }

        return $this;
    }

    public function removeUserPokemon(UserPokemon $userPokemon): self
    {
        if ($this->userPokemon->removeElement($userPokemon)) {
            // set the owning side to null (unless already changed)
            if ($userPokemon->getUser() === $this) {
                $userPokemon->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->username;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, UserPvPPokemon>
     */
    public function getUserPvPPokemon(): Collection
    {
        return $this->userPvPPokemon;
    }

    public function addUserPvPPokemon(UserPvPPokemon $userPvPPokemon): self
    {
        if (!$this->userPvPPokemon->contains($userPvPPokemon)) {
            $this->userPvPPokemon->add($userPvPPokemon);
            $userPvPPokemon->setUser($this);
        }

        return $this;
    }

    public function removeUserPvPPokemon(UserPvPPokemon $userPvPPokemon): self
    {
        if ($this->userPvPPokemon->removeElement($userPvPPokemon)) {
            // set the owning side to null (unless already changed)
            if ($userPvPPokemon->getUser() === $this) {
                $userPvPPokemon->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PvPQuiz>
     */
    public function getPvpQuizzes(): Collection
    {
        return $this->pvpQuizzes;
    }

    public function addPvpQuiz(PvPQuiz $pvpQuiz): static
    {
        if (!$this->pvpQuizzes->contains($pvpQuiz)) {
            $this->pvpQuizzes->add($pvpQuiz);
            $pvpQuiz->setUser($this);
        }

        return $this;
    }

    public function removePvpQuiz(PvPQuiz $pvpQuiz): static
    {
        if ($this->pvpQuizzes->removeElement($pvpQuiz)) {
            // set the owning side to null (unless already changed)
            if ($pvpQuiz->getUser() === $this) {
                $pvpQuiz->setUser(null);
            }
        }

        return $this;
    }
}
