<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PokemonMoveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonMoveRepository::class)]
class PokemonMove
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pokemonMoves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\ManyToOne(inversedBy: 'pokemonMoves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Move $move = null;

    #[ORM\Column]
    private bool $elite = false;

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

    public function getMove(): ?Move
    {
        return $this->move;
    }

    public function setMove(?Move $move): static
    {
        $this->move = $move;

        return $this;
    }

    public function isElite(): bool
    {
        return $this->elite;
    }

    public function setElite(bool $elite): static
    {
        $this->elite = $elite;

        return $this;
    }

    public function __toString(): string
    {
        $moveName = $this->move?->getName() ?? 'UNKNOWN';
        $elite = $this->elite ? ' (Elite)' : '';

        return $moveName . $elite;
    }
}
