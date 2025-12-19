<?php

namespace App\Entity;

use App\Contract\Trait\TimestampableTrait;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\UniqueConstraint(name: 'slug_uniq', columns: ['slug'])]
#[HasLifecycleCallbacks]
class Type
{
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Move>
     */
    #[ORM\OneToMany(targetEntity: Move::class, mappedBy: 'type', orphanRemoval: true)]
    private Collection $moves;

    /**
     * @var Collection<int, Pokemon>
     */
    #[ORM\ManyToMany(targetEntity: Pokemon::class, mappedBy: 'types')]
    private Collection $pokemon;

    /**
     * @var Collection<int, TypeEffectiveness>
     */
    #[ORM\OneToMany(targetEntity: TypeEffectiveness::class, mappedBy: 'sourceType')]
    private Collection $effectiveness;

    public function __construct()
    {
        $this->moves = new ArrayCollection();
        $this->pokemon = new ArrayCollection();
        $this->effectiveness = new ArrayCollection();
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

    /**
     * @return Collection<int, Pokemon>
     */
    public function getPokemon(): Collection
    {
        return $this->pokemon;
    }

    public function addPokemon(Pokemon $pokemon): static
    {
        if (!$this->pokemon->contains($pokemon)) {
            $this->pokemon->add($pokemon);
            $pokemon->addType($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): static
    {
        if ($this->pokemon->removeElement($pokemon)) {
            $pokemon->removeType($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeEffectiveness>
     */
    public function getEffectiveness(): Collection
    {
        return $this->effectiveness;
    }

    public function addEffectiveness(TypeEffectiveness $effectiveness): static
    {
        if (!$this->effectiveness->contains($effectiveness)) {
            $this->effectiveness->add($effectiveness);
            $effectiveness->setSourceType($this);
        }

        return $this;
    }

    public function removeEffectiveness(TypeEffectiveness $effectiveness): static
    {
        if ($this->effectiveness->removeElement($effectiveness)) {
            // set the owning side to null (unless already changed)
            if ($effectiveness->getSourceType() === $this) {
                $effectiveness->setSourceType(null);
            }
        }

        return $this;
    }
}
