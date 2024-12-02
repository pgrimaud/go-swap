<?php

namespace App\Entity;

use App\Repository\UserPvPPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPvPPokemonRepository::class)]
#[ORM\Table(name: 'user_pvp_pokemon')]
#[ORM\UniqueConstraint(
    name: 'pokemon_user_uniq',
    columns: ['pokemon_id', 'user_id'],
)]
class UserPvPPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Pokemon::class, inversedBy: 'userPvPPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userPvPPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $littleCupRank = null;

    #[ORM\Column]
    private ?int $greatLeagueRank = null;

    #[ORM\Column]
    private ?int $ultraLeagueRank = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $hidden = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon): static
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLittleCupRank(): ?int
    {
        return $this->littleCupRank;
    }

    public function setLittleCupRank(int $littleCupRank): static
    {
        $this->littleCupRank = $littleCupRank;

        return $this;
    }

    public function getGreatLeagueRank(): ?int
    {
        return $this->greatLeagueRank;
    }

    public function setGreatLeagueRank(int $greatLeagueRank): static
    {
        $this->greatLeagueRank = $greatLeagueRank;

        return $this;
    }

    public function getUltraLeagueRank(): ?int
    {
        return $this->ultraLeagueRank;
    }

    public function setUltraLeagueRank(int $ultraLeagueRank): static
    {
        $this->ultraLeagueRank = $ultraLeagueRank;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }
}
