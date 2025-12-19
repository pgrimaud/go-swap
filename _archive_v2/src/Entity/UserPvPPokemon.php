<?php

namespace App\Entity;

use App\Enum\League;
use App\Enum\Type;
use App\Repository\UserPvPPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPvPPokemonRepository::class)]
#[ORM\Table(name: 'user_pvp_pokemon')]
class UserPvPPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userPvPPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userPvPPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Move $fastMove = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Move $chargedMove1 = null;

    #[ORM\ManyToOne]
    private ?Move $chargedMove2 = null;

    #[ORM\Column(length: 255)]
    private string $type = Type::TYPE_NORMAL;

    #[ORM\Column(length: 255)]
    private string $league = League::GREAT_LEAGUE;

    #[ORM\Column]
    private int $attack = 0;

    #[ORM\Column]
    private int $defense = 0;

    #[ORM\Column]
    private int $stamina = 0;

    #[ORM\Column]
    private int $leagueRank = 0;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon): static
    {
        $this->pokemon = $pokemon;

        return $this;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAttack(): int
    {
        return $this->attack;
    }

    public function setAttack(int $attack): static
    {
        $this->attack = $attack;

        return $this;
    }

    public function getDefense(): int
    {
        return $this->defense;
    }

    public function setDefense(int $defense): static
    {
        $this->defense = $defense;

        return $this;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): static
    {
        $this->stamina = $stamina;

        return $this;
    }

    public function getLeague(): string
    {
        return $this->league;
    }

    public function setLeague(string $league): void
    {
        $this->league = $league;
    }

    public function getLeagueRank(): int
    {
        return $this->leagueRank;
    }

    public function setLeagueRank(int $leagueRank): void
    {
        $this->leagueRank = $leagueRank;
    }
}
