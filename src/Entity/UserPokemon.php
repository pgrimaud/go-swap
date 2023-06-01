<?php

namespace App\Entity;

use App\Repository\UserPokemonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserPokemonRepository::class)]
class UserPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private User|UserInterface|null $user = null;

    #[ORM\ManyToOne(inversedBy: 'userPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\Column]
    private ?bool $normal = null;

    #[ORM\Column]
    private ?bool $shiny = null;

    #[ORM\Column]
    private ?bool $lucky = null;

    #[ORM\Column]
    private ?bool $threeStars = null;

    #[ORM\Column(nullable: true)]
    private ?int $numberShiny = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User|UserInterface|null
    {
        return $this->user;
    }

    public function setUser(User|UserInterface|null $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function isNormal(): ?bool
    {
        return $this->normal;
    }

    public function setNormal(bool $normal): self
    {
        $this->normal = $normal;

        return $this;
    }

    public function isShiny(): ?bool
    {
        return $this->shiny;
    }

    public function setShiny(bool $shiny): self
    {
        $this->shiny = $shiny;

        return $this;
    }

    public function isLucky(): ?bool
    {
        return $this->lucky;
    }

    public function setLucky(bool $lucky): self
    {
        $this->lucky = $lucky;

        return $this;
    }

    public function isThreeStars(): ?bool
    {
        return $this->threeStars;
    }

    public function setThreeStars(bool $threeStars): self
    {
        $this->threeStars = $threeStars;

        return $this;
    }

    public function getNumberShiny(): ?int
    {
        return $this->numberShiny;
    }

    public function setNumberShiny(?int $numberShiny): self
    {
        $this->numberShiny = $numberShiny;

        return $this;
    }
}
