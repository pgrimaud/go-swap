<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\PvPVersionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PvPVersionRepository::class)]
#[ORM\Table(name: 'pvp_version')]
#[ORM\HasLifecycleCallbacks]
class PvPVersion
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

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
}
