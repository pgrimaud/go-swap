<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
class Pokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column(length: 255)]
    private ?string $generation = null;

    #[ORM\Column(length: 255)]
    private ?string $frenchName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $englishName = null;

    #[ORM\Column]
    private ?bool $isShiny = null;

    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: UserPokemon::class, orphanRemoval: true)]
    private Collection $userPokemon;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $normalPicture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shinyPicture = null;

    #[ORM\Column]
    private ?bool $isActual = false;

    #[ORM\ManyToOne(inversedBy: 'pokemons')]
    private ?EvolutionChain $evolutionChain = null;

    #[ORM\Column]
    private ?bool $isLucky = true;

    #[ORM\Column]
    private ?bool $isShadow = false;

    #[ORM\Column]
    private ?bool $isPurified = false;

    #[ORM\Column]
    private ?bool $isShinyThreeStars = null;

    #[ORM\Column(nullable: true)]
    private ?int $evolutionChainPosition = 0;

    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: UserPvPPokemon::class, orphanRemoval: true)]
    private Collection $userPvPPokemon;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, inversedBy: 'pokemons')]
    private Collection $types;

    #[ORM\Column(length: 255)]
    private ?string $form = null;

    /**
     * @var Collection<int, PokemonMove>
     */
    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: PokemonMove::class, orphanRemoval: true)]
    private Collection $pokemonMoves;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->userPokemon = new ArrayCollection();
        $this->userPvPPokemon = new ArrayCollection();
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

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getGeneration(): ?string
    {
        return $this->generation;
    }

    public function setGeneration(string $generation): self
    {
        $this->generation = $generation;

        return $this;
    }

    public function getFrenchName(): ?string
    {
        return $this->frenchName;
    }

    public function setFrenchName(string $frenchName): self
    {
        $this->frenchName = $frenchName;

        return $this;
    }

    public function getEnglishName(): ?string
    {
        return $this->englishName;
    }

    public function setEnglishName(?string $englishName): self
    {
        $this->englishName = $englishName;

        return $this;
    }

    public function isIsShiny(): ?bool
    {
        return $this->isShiny;
    }

    public function setIsShiny(bool $isShiny): self
    {
        $this->isShiny = $isShiny;

        return $this;
    }

    /**
     * @return Collection<int, UserPokemon>
     */
    public function getUserPokemon(): Collection
    {
        return $this->userPokemon;
    }

    public function addUserPokemon(UserPokemon $userPokemon): self
    {
        if (!$this->userPokemon->contains($userPokemon)) {
            $this->userPokemon->add($userPokemon);
            $userPokemon->setPokemon($this);
        }

        return $this;
    }

    public function removeUserPokemon(UserPokemon $userPokemon): self
    {
        if ($this->userPokemon->removeElement($userPokemon)) {
            // set the owning side to null (unless already changed)
            if ($userPokemon->getPokemon() === $this) {
                $userPokemon->setPokemon(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->frenchName;
    }

    public function getNormalPicture(): ?string
    {
        return $this->normalPicture;
    }

    public function setNormalPicture(?string $normalPicture): self
    {
        $this->normalPicture = $normalPicture;

        return $this;
    }

    public function getShinyPicture(): ?string
    {
        return $this->shinyPicture;
    }

    public function setShinyPicture(?string $shinyPicture): self
    {
        $this->shinyPicture = $shinyPicture;

        return $this;
    }

    public function isIsActual(): ?bool
    {
        return $this->isActual;
    }

    public function setIsActual(bool $isActual): self
    {
        $this->isActual = $isActual;

        return $this;
    }

    public function getEvolutionChain(): ?EvolutionChain
    {
        return $this->evolutionChain;
    }

    public function setEvolutionChain(?EvolutionChain $evolutionChain): self
    {
        $this->evolutionChain = $evolutionChain;

        return $this;
    }

    public function isIsLucky(): ?bool
    {
        return $this->isLucky;
    }

    public function setIsLucky(bool $isLucky): self
    {
        $this->isLucky = $isLucky;

        return $this;
    }

    public function getIsShadow(): ?bool
    {
        return $this->isShadow;
    }

    public function setIsShadow(?bool $isShadow): void
    {
        $this->isShadow = $isShadow;
    }

    public function getIsPurified(): ?bool
    {
        return $this->isPurified;
    }

    public function setIsPurified(?bool $isPurified): void
    {
        $this->isPurified = $isPurified;
    }

    public function getIsShinyThreeStars(): ?bool
    {
        return $this->isShinyThreeStars;
    }

    public function setIsShinyThreeStars(?bool $isShinyThreeStars): void
    {
        $this->isShinyThreeStars = $isShinyThreeStars;
    }

    public function getEvolutionChainPosition(): ?int
    {
        return $this->evolutionChainPosition;
    }

    public function setEvolutionChainPosition(?int $evolutionChainPosition): static
    {
        $this->evolutionChainPosition = $evolutionChainPosition;

        return $this;
    }

    /**
     * @return Collection<int, UserPvPPokemon>
     */
    public function getUserPvPPokemon(): Collection
    {
        return $this->userPvPPokemon;
    }

    public function addUserPvPPokemon(UserPvPPokemon $userPvPPokemon): self
    {
        if (!$this->userPvPPokemon->contains($userPvPPokemon)) {
            $this->userPvPPokemon->add($userPvPPokemon);
            $userPvPPokemon->setPokemon($this);
        }

        return $this;
    }

    public function removeUserPvPPokemon(UserPvPPokemon $userPvPPokemon): self
    {
        if ($this->userPvPPokemon->removeElement($userPvPPokemon)) {
            // set the owning side to null (unless already changed)
            if ($userPvPPokemon->getPokemon() === $this) {
                $userPvPPokemon->setPokemon(null);
            }
        }

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

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function setForm(string $form): static
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return Collection<int, PokemonMove>
     */
    public function getPokemonMoves(): Collection
    {
        return $this->pokemonMoves;
    }

    public function getFastMoves(): Collection
    {
        return $this->pokemonMoves->filter(function (PokemonMove $pokemonMove) {
            return $pokemonMove->getMove()?->getAttackType() === Move::FAST_MOVE;
        });
    }

    public function getChargedMoves(): Collection
    {
        return $this->pokemonMoves->filter(function (PokemonMove $pokemonMove) {
            return $pokemonMove->getMove()?->getAttackType() === Move::CHARGED_MOVE;
        });
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

    public function removeAllPokemonMoves(): static
    {
        foreach ($this->pokemonMoves as $pokemonMove) {
            $this->removePokemonMove($pokemonMove);
        }

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
}
