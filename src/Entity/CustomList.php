<?php

declare(strict_types=1);

namespace App\Entity;

use App\Contract\Trait\TimestampTrait;
use App\Repository\CustomListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CustomListRepository::class)]
#[ORM\UniqueConstraint(name: 'custom_list_uid_unique', columns: ['uid'])]
#[HasLifecycleCallbacks]
class CustomList
{
    use TimestampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private string $uid;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPublic = false;

    /**
     * @var Collection<int, CustomListPokemon>
     */
    #[ORM\OneToMany(targetEntity: CustomListPokemon::class, mappedBy: 'customList', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $customListPokemon;

    public function __construct()
    {
        $this->uid = Uuid::v4()->toRfc4122();
        $this->customListPokemon = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return Collection<int, CustomListPokemon>
     */
    public function getCustomListPokemon(): Collection
    {
        return $this->customListPokemon;
    }

    /**
     * @return array<CustomListPokemon>
     */
    public function getCustomListPokemonSortedByNumber(): array
    {
        $pokemon = $this->customListPokemon->toArray();
        usort(
            $pokemon,
            fn (CustomListPokemon $a, CustomListPokemon $b) => ($a->getPokemon()?->getNumber() ?? 9999) <=> ($b->getPokemon()?->getNumber() ?? 9999)
        );

        return $pokemon;
    }

    public function addCustomListPokemon(CustomListPokemon $customListPokemon): static
    {
        if (!$this->customListPokemon->contains($customListPokemon)) {
            $this->customListPokemon->add($customListPokemon);
            $customListPokemon->setCustomList($this);
        }

        return $this;
    }

    public function removeCustomListPokemon(CustomListPokemon $customListPokemon): static
    {
        if ($this->customListPokemon->removeElement($customListPokemon)) {
            if ($customListPokemon->getCustomList() === $this) {
                $customListPokemon->setCustomList(null);
            }
        }

        return $this;
    }

    public function getPokemonCount(): int
    {
        return $this->customListPokemon->count();
    }

    /**
     * @return array<Pokemon>
     */
    public function getPokemons(): array
    {
        return array_values(array_filter(
            $this->customListPokemon
                ->map(fn (CustomListPokemon $clp) => $clp->getPokemon())
                ->toArray(),
            fn ($pokemon) => $pokemon !== null
        ));
    }

    public function getSearchString(): string
    {
        $numbers = $this->customListPokemon
            ->map(fn (CustomListPokemon $clp) => $clp->getPokemon()?->getNumber())
            ->filter(fn ($number) => $number !== null)
            ->toArray();

        return implode(',', $numbers);
    }
}
