<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
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
    private ?string $french_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $english_name = null;

    #[ORM\Column]
    private ?bool $isShiny = null;

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
        return $this->french_name;
    }

    public function setFrenchName(string $french_name): self
    {
        $this->french_name = $french_name;

        return $this;
    }

    public function getEnglishName(): ?string
    {
        return $this->english_name;
    }

    public function setEnglishName(?string $english_name): self
    {
        $this->english_name = $english_name;

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
}
