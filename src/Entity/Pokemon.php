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

    public function __construct()
    {
        $this->userPokemon = new ArrayCollection();
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
}
