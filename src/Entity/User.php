<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, UserPvPPokemon>
     */
    #[ORM\OneToMany(targetEntity: UserPvPPokemon::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userPvPPokemon;

    public function __construct()
    {
        $this->userPvPPokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        if (!$this->email) {
            throw new \LogicException('User email is not set.');
        }

        return $this->email;
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

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
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

    public function setPassword(string $password): static
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
     * @return Collection<int, UserPvPPokemon>
     */
    public function getUserPvPPokemon(): Collection
    {
        return $this->userPvPPokemon;
    }

    public function addUserPvPPokemon(UserPvPPokemon $userPvPPokemon): static
    {
        if (!$this->userPvPPokemon->contains($userPvPPokemon)) {
            $this->userPvPPokemon->add($userPvPPokemon);
            $userPvPPokemon->setUser($this);
        }

        return $this;
    }

    public function removeUserPvPPokemon(UserPvPPokemon $userPvPPokemon): static
    {
        if ($this->userPvPPokemon->removeElement($userPvPPokemon)) {
            // set the owning side to null (unless already changed)
            if ($userPvPPokemon->getUser() === $this) {
                $userPvPPokemon->setUser(null);
            }
        }

        return $this;
    }
}
