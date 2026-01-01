<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\PvPRankingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PvPRankingRepository::class)]
#[ORM\Table(name: 'pvp_ranking')]
#[ORM\UniqueConstraint(name: 'version_pokemon_league_uniq', columns: ['pvp_version_id', 'pokemon_id', 'league'])]
#[ORM\HasLifecycleCallbacks]
class PvPRanking
{
    use TimestampTrait;

    public const string LEAGUE_GREAT = 'great';
    public const string LEAGUE_ULTRA = 'ultra';
    public const string LEAGUE_MASTER = 'master';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PvPVersion $pvpVersion = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\Column(length: 20)]
    private ?string $league = null;

    #[ORM\Column(name: '`rank`')]
    private ?int $rank = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $score = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Move $fastMove = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Move $chargedMove1 = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Move $chargedMove2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPvpVersion(): ?PvPVersion
    {
        return $this->pvpVersion;
    }

    public function setPvpVersion(?PvPVersion $pvpVersion): static
    {
        $this->pvpVersion = $pvpVersion;

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

    public function getLeague(): ?string
    {
        return $this->league;
    }

    public function setLeague(string $league): static
    {
        $this->league = $league;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): static
    {
        $this->score = $score;

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
}
