<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CustomListPokemonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomListPokemonRepository::class)]
#[ORM\UniqueConstraint(name: 'custom_list_pokemon_unique', columns: ['custom_list_id', 'pokemon_id'])]
class CustomListPokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CustomList::class, inversedBy: 'customListPokemon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomList $customList = null;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(options: ['default' => false])]
    private bool $isShiny = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $addedAt = null;

    public function __construct()
    {
        $this->addedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomList(): ?CustomList
    {
        return $this->customList;
    }

    public function setCustomList(?CustomList $customList): static
    {
        $this->customList = $customList;

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeImmutable $addedAt): static
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function isShiny(): bool
    {
        return $this->isShiny;
    }

    public function setIsShiny(bool $isShiny): static
    {
        $this->isShiny = $isShiny;

        return $this;
    }
}
