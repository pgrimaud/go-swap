<?php

namespace App\Entity;

use App\Repository\MoveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoveRepository::class)]
class Move
{
    public const FAST_MOVE = 'fast';
    public const CHARGED_MOVE = 'charged';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'moves')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $Type = null;

    #[ORM\Column(length: 255)]
    private ?string $attackType = null;

    #[ORM\Column]
    private ?int $power = null;

    #[ORM\Column]
    private ?int $turnDuration = null;

    #[ORM\Column]
    private ?int $energyDelta = null;

    /**
     * @var Collection<int, PokemonMove>
     */
    #[ORM\OneToMany(mappedBy: 'move', targetEntity: PokemonMove::class, orphanRemoval: true)]
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

    public function getType(): ?Type
    {
        return $this->Type;
    }

    public function setType(?Type $Type): static
    {
        $this->Type = $Type;

        return $this;
    }

    public function getAttackType(): ?string
    {
        return $this->attackType;
    }

    public function setAttackType(string $attackType): static
    {
        $this->attackType = $attackType;

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

    public function getTurnDuration(): ?int
    {
        return $this->turnDuration;
    }

    public function setTurnDuration(int $turnDuration): static
    {
        $this->turnDuration = $turnDuration;

        return $this;
    }

    public function getEnergyDelta(): ?int
    {
        return $this->energyDelta;
    }

    public function setEnergyDelta(int $energyDelta): static
    {
        $this->energyDelta = $energyDelta;

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
}
