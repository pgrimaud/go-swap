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
}
