<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\MoveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[ORM\Entity(repositoryClass: MoveRepository::class)]
#[ORM\UniqueConstraint(name: 'slug_uniq', columns: ['slug'])]
#[HasLifecycleCallbacks]
class Move
{
    use TimestampTrait;

    public const string BUFF_TARGET_SELF = 'self';
    public const string BUFF_TARGET_OPPONENT = 'opponent';

    public const string FAST_MOVE = 'fast';
    public const string CHARGED_MOVE = 'charged';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'moves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $type = null;

    #[ORM\Column]
    private ?int $power = null;

    #[ORM\Column]
    private ?int $energy = null;

    #[ORM\Column]
    private ?int $energyGain = null;

    #[ORM\Column]
    private ?int $cooldown = null;

    #[ORM\Column(nullable: true)]
    private ?int $buffAttack = null;

    #[ORM\Column(nullable: true)]
    private ?int $buffDefense = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $buffTarget = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $buffChance = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $class = null;

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    /**
     * @var Collection<int, PokemonMove>
     */
    #[ORM\OneToMany(targetEntity: PokemonMove::class, mappedBy: 'move', orphanRemoval: true)]
    private Collection $pokemonMoves;

    public function __construct()
    {
        $this->pokemonMoves = new ArrayCollection();
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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(int $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getEnergy(): ?int
    {
        return $this->energy;
    }

    public function setEnergy(int $energy): static
    {
        $this->energy = $energy;

        return $this;
    }

    public function getEnergyGain(): ?int
    {
        return $this->energyGain;
    }

    public function setEnergyGain(int $energyGain): static
    {
        $this->energyGain = $energyGain;

        return $this;
    }

    public function getCooldown(): ?int
    {
        return $this->cooldown;
    }

    public function setCooldown(int $cooldown): static
    {
        $this->cooldown = $cooldown;

        return $this;
    }

    public function getBuffAttack(): ?int
    {
        return $this->buffAttack;
    }

    public function setBuffAttack(?int $buffAttack): static
    {
        $this->buffAttack = $buffAttack;

        return $this;
    }

    public function getBuffDefense(): ?int
    {
        return $this->buffDefense;
    }

    public function setBuffDefense(?int $buffDefense): static
    {
        $this->buffDefense = $buffDefense;

        return $this;
    }

    public function getBuffTarget(): ?string
    {
        return $this->buffTarget;
    }

    public function setBuffTarget(?string $buffTarget): static
    {
        $this->buffTarget = $buffTarget;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getBuffChance(): ?float
    {
        return $this->buffChance;
    }

    public function setBuffChance(?float $buffChance): static
    {
        $this->buffChance = $buffChance;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return Collection<int, PokemonMove>
     */
    public function getPokemonMoves(): Collection
    {
        return $this->pokemonMoves;
    }

    public function addPokemonMove(PokemonMove $pokemonMove): static
    {
        if (!$this->pokemonMoves->contains($pokemonMove)) {
            $this->pokemonMoves->add($pokemonMove);
            $pokemonMove->setMove($this);
        }

        return $this;
    }

    public function removePokemonMove(PokemonMove $pokemonMove): static
    {
        if ($this->pokemonMoves->removeElement($pokemonMove)) {
            // set the owning side to null (unless already changed)
            if ($pokemonMove->getMove() === $this) {
                $pokemonMove->setMove(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'UNKNOWN';
    }
}
