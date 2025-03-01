<?php

namespace App\Entity;

use App\Repository\UserPvPPokemonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPvPPokemonRepository::class)]
#[ORM\Table(name: 'user_pvp_pokemon')]
class UserPvPPokemon
{
    public const string LITTLE_CUP = 'little_cup';
    public const string GREAT_LEAGUE = 'great_league';
    public const string ULTRA_LEAGUE = 'ultra_league';

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
    private ?int $rank = 0;

    #[ORM\Column(type: Types::STRING, nullable: false)]
    private ?string $league = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Move $fastMove = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Move $chargedMove1 = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Move $chargedMove2 = null;

    #[ORM\Column]
    private ?bool $shadow = null;

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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): void
    {
        $this->rank = $rank;
    }

    public function getLeague(): ?string
    {
        return $this->league;
    }

    public function setLeague(string $league): void
    {
        $this->league = $league;
    }

    public function getFastMove(): ?Move
    {
        return $this->fastMove;
    }

    public function setFastMove(?Move $fastMove): static
    {
        $this->fastMove = $fastMove;

        return $this;
    }

    public function getChargedMove1(): ?Move
    {
        return $this->chargedMove1;
    }

    public function setChargedMove1(?Move $chargedMove1): static
    {
        $this->chargedMove1 = $chargedMove1;

        return $this;
    }

    public function getChargedMove2(): ?Move
    {
        return $this->chargedMove2;
    }

    public function setChargedMove2(?Move $chargedMove2): static
    {
        $this->chargedMove2 = $chargedMove2;

        return $this;
    }

    public function isShadow(): ?bool
    {
        return $this->shadow;
    }

    public function setShadow(bool $shadow): static
    {
        $this->shadow = $shadow;

        return $this;
    }
}
