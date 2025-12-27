<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\UserPokemonRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[ORM\Entity(repositoryClass: UserPokemonRepository::class)]
#[ORM\UniqueConstraint(name: 'user_pokemon_unique', columns: ['user_id', 'pokemon_id'])]
#[HasLifecycleCallbacks]
class UserPokemon
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $pokemon = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasNormal = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasShiny = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasShadow = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasPurified = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasLucky = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasXxl = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasXxs = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasPerfect = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $firstCaughtAt = null;

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

    public function hasNormal(): bool
    {
        return $this->hasNormal;
    }

    public function setHasNormal(bool $hasNormal): static
    {
        $this->hasNormal = $hasNormal;

        return $this;
    }

    public function hasShiny(): bool
    {
        return $this->hasShiny;
    }

    public function setHasShiny(bool $hasShiny): static
    {
        $this->hasShiny = $hasShiny;

        return $this;
    }

    public function hasShadow(): bool
    {
        return $this->hasShadow;
    }

    public function setHasShadow(bool $hasShadow): static
    {
        $this->hasShadow = $hasShadow;

        return $this;
    }

    public function hasPurified(): bool
    {
        return $this->hasPurified;
    }

    public function setHasPurified(bool $hasPurified): static
    {
        $this->hasPurified = $hasPurified;

        return $this;
    }

    public function hasLucky(): bool
    {
        return $this->hasLucky;
    }

    public function setHasLucky(bool $hasLucky): static
    {
        $this->hasLucky = $hasLucky;

        return $this;
    }

    public function hasXxl(): bool
    {
        return $this->hasXxl;
    }

    public function setHasXxl(bool $hasXxl): static
    {
        $this->hasXxl = $hasXxl;

        return $this;
    }

    public function hasXxs(): bool
    {
        return $this->hasXxs;
    }

    public function setHasXxs(bool $hasXxs): static
    {
        $this->hasXxs = $hasXxs;

        return $this;
    }

    public function hasPerfect(): bool
    {
        return $this->hasPerfect;
    }

    public function setHasPerfect(bool $hasPerfect): static
    {
        $this->hasPerfect = $hasPerfect;

        return $this;
    }

    public function getFirstCaughtAt(): ?\DateTimeImmutable
    {
        return $this->firstCaughtAt;
    }

    public function setFirstCaughtAt(\DateTimeImmutable $firstCaughtAt): static
    {
        $this->firstCaughtAt = $firstCaughtAt;

        return $this;
    }
}
