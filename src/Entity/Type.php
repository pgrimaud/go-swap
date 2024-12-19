<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Pokemon>
     */
    #[ORM\ManyToMany(targetEntity: Pokemon::class, mappedBy: 'types')]
    private Collection $pokemons;

    /**
     * @var Collection<int, TypeEffectiveness>
     */
    #[ORM\OneToMany(mappedBy: 'sourceType', targetEntity: TypeEffectiveness::class, orphanRemoval: true)]
    private Collection $effectivenesses;

    /**
     * @var Collection<int, Move>
     */
    #[ORM\OneToMany(mappedBy: 'Type', targetEntity: Move::class, orphanRemoval: true)]
    private Collection $moves;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
        $this->effectivenesses = new ArrayCollection();
        $this->moves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Pokemon>
     */
    public function getPokemons(): Collection
    {
        return $this->pokemons;
    }

    public function addPokemon(Pokemon $pokemon): static
    {
        if (!$this->pokemons->contains($pokemon)) {
            $this->pokemons->add($pokemon);
            $pokemon->addType($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): static
    {
        if ($this->pokemons->removeElement($pokemon)) {
            $pokemon->removeType($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return Collection<int, TypeEffectiveness>
     */
    public function getEffectivenesses(): Collection
    {
        return $this->effectivenesses;
    }

    public function addEffectiveness(TypeEffectiveness $effectiveness): static
    {
        if (!$this->effectivenesses->contains($effectiveness)) {
            $this->effectivenesses->add($effectiveness);
            $effectiveness->setSourceType($this);
        }

        return $this;
    }

    public function removeEffectiveness(TypeEffectiveness $effectiveness): static
    {
        if ($this->effectivenesses->removeElement($effectiveness)) {
            // set the owning side to null (unless already changed)
            if ($effectiveness->getSourceType() === $this) {
                $effectiveness->setSourceType(null);
            }
        }

        return $this;
    }

    public function getStrongAgainst(): Collection
    {
        return $this->effectivenesses->filter(function (TypeEffectiveness $effectiveness) {
            return $effectiveness->getMultiplier() > 1;
        });
    }

    public function getBestDefender(): Collection
    {
        return $this->effectivenesses->filter(function (TypeEffectiveness $effectiveness) {
            return $effectiveness->getMultiplier() < 1;
        });
    }

    /**
     * @return Collection<int, Move>
     */
    public function getMoves(): Collection
    {
        return $this->moves;
    }

    public function addMove(Move $move): static
    {
        if (!$this->moves->contains($move)) {
            $this->moves->add($move);
            $move->setType($this);
        }

        return $this;
    }

    public function removeMove(Move $move): static
    {
        if ($this->moves->removeElement($move)) {
            // set the owning side to null (unless already changed)
            if ($move->getType() === $this) {
                $move->setType(null);
            }
        }

        return $this;
    }
}
