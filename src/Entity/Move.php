<?php

namespace App\Entity;

use App\Repository\MoveRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoveRepository::class)]
#[ORM\UniqueConstraint(name: 'api_id_uniq', columns: ['api_id'])]
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

    #[ORM\Column]
    private ?int $apiId = null;

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

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): static
    {
        $this->apiId = $apiId;

        return $this;
    }
}
