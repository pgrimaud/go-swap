<?php

namespace App\Entity;

use App\Repository\TypeEffectivenessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeEffectivenessRepository::class)]
class TypeEffectiveness
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'effectivenesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $sourceType = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Type $targetType = null;

    #[ORM\Column]
    private ?float $multiplier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceType(): ?Type
    {
        return $this->sourceType;
    }

    public function setSourceType(?Type $sourceType): static
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    public function getTargetType(): ?Type
    {
        return $this->targetType;
    }

    public function setTargetType(?Type $targetType): static
    {
        $this->targetType = $targetType;

        return $this;
    }

    public function getMultiplier(): ?float
    {
        return $this->multiplier;
    }

    public function setMultiplier(float $multiplier): static
    {
        $this->multiplier = $multiplier;

        return $this;
    }
}
