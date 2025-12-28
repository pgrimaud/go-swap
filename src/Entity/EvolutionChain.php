<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\EvolutionChainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EvolutionChainRepository::class)]
#[ORM\UniqueConstraint(name: 'chain_id_uniq', columns: ['chain_id'])]
#[HasLifecycleCallbacks]
class EvolutionChain
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['evolution_chain:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['evolution_chain:read'])]
    private ?int $chainId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['evolution_chain:read'])]
    private ?string $basePokemonName = null;

    /**
     * @var Collection<int, Pokemon>
     */
    #[ORM\OneToMany(targetEntity: Pokemon::class, mappedBy: 'evolutionChain')]
    #[Groups(['evolution_chain:read'])]
    private Collection $pokemon;

    public function __construct()
    {
        $this->pokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChainId(): ?int
    {
        return $this->chainId;
    }

    public function setChainId(int $chainId): static
    {
        $this->chainId = $chainId;

        return $this;
    }

    public function getBasePokemonName(): ?string
    {
        return $this->basePokemonName;
    }

    public function setBasePokemonName(?string $basePokemonName): static
    {
        $this->basePokemonName = $basePokemonName;

        return $this;
    }

    /**
     * @return Collection<int, Pokemon>
     */
    public function getPokemon(): Collection
    {
        return $this->pokemon;
    }

    public function addPokemon(Pokemon $pokemon): static
    {
        if (!$this->pokemon->contains($pokemon)) {
            $this->pokemon->add($pokemon);
            $pokemon->setEvolutionChain($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): static
    {
        if ($this->pokemon->removeElement($pokemon)) {
            if ($pokemon->getEvolutionChain() === $this) {
                $pokemon->setEvolutionChain(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        $name = $this->basePokemonName ?? 'Unknown';

        return sprintf('Evolution Chain #%d (%s)', $this->chainId ?? 0, $name);
    }
}
