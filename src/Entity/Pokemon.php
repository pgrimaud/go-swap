<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
#[ORM\UniqueConstraint(name: 'slug_uniq', columns: ['slug'])]
#[HasLifecycleCallbacks]
class Pokemon
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pokemon:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['pokemon:read'])]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pokemon:read'])]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $attack = null;

    #[ORM\Column]
    private ?int $defense = null;

    #[ORM\Column]
    private ?int $stamina = null;

    #[ORM\Column(length: 255)]
    private ?string $hash = null;

    #[ORM\Column]
    private bool $shadow = false;

    #[ORM\Column]
    private bool $shiny = false;

    #[ORM\Column]
    private bool $lucky = false;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'pokemon')]
    private Collection $types;

    /**
     * @var Collection<int, PokemonMove>
     */
    #[ORM\OneToMany(targetEntity: PokemonMove::class, mappedBy: 'pokemon', cascade: ['persist'], orphanRemoval: true)]
    private Collection $pokemonMoves;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pokemon:read'])]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pokemon:read'])]
    private ?string $shinyPicture = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pokemon:read'])]
    private ?string $generation = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pokemon:read'])]
    private ?string $form = null;

    public function __construct()
    {
        $this->types = new ArrayCollection();
        $this->pokemonMoves = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
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

    public function getAttack(): ?int
    {
        return $this->attack;
    }

    public function setAttack(int $attack): static
    {
        $this->attack = $attack;

        return $this;
    }

    public function getDefense(): ?int
    {
        return $this->defense;
    }

    public function setDefense(int $defense): static
    {
        $this->defense = $defense;

        return $this;
    }

    public function getStamina(): ?int
    {
        return $this->stamina;
    }

    public function setStamina(int $stamina): static
    {
        $this->stamina = $stamina;

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

    public function isShadow(): bool
    {
        return $this->shadow;
    }

    public function setShadow(bool $shadow): static
    {
        $this->shadow = $shadow;

        return $this;
    }

    public function isShiny(): bool
    {
        return $this->shiny;
    }

    public function setShiny(bool $shiny): static
    {
        $this->shiny = $shiny;

        return $this;
    }

    public function isLucky(): bool
    {
        return $this->lucky;
    }

    public function setLucky(bool $lucky): static
    {
        $this->lucky = $lucky;

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

    /**
     * @return Collection<int, Type>
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(Type $type): static
    {
        if (!$this->types->contains($type)) {
            $this->types->add($type);
        }

        return $this;
    }

    public function removeType(Type $type): static
    {
        $this->types->removeElement($type);

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
            $pokemonMove->setPokemon($this);
        }

        return $this;
    }

    public function removePokemonMove(PokemonMove $pokemonMove): static
    {
        if ($this->pokemonMoves->removeElement($pokemonMove)) {
            // set the owning side to null (unless already changed)
            if ($pokemonMove->getPokemon() === $this) {
                $pokemonMove->setPokemon(null);
            }
        }

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getShinyPicture(): ?string
    {
        return $this->shinyPicture;
    }

    public function setShinyPicture(?string $shinyPicture): static
    {
        $this->shinyPicture = $shinyPicture;

        return $this;
    }

    public function getGeneration(): ?string
    {
        return $this->generation;
    }

    public function setGeneration(string $generation): static
    {
        $this->generation = $generation;

        return $this;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function setForm(?string $form): static
    {
        $this->form = $form;

        return $this;
    }
}
