<?php

namespace App\Entity;

use App\Repository\UserPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPokemonRepository::class)]
class UserPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
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
}
