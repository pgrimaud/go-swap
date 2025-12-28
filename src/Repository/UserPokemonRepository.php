<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserPokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPokemon>
 */
class UserPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPokemon::class);
    }

    public function findByUserAndPokemon(User $user, Pokemon $pokemon): ?UserPokemon
    {
        /** @var UserPokemon|null $result */
        $result = $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->andWhere('up.pokemon = :pokemon')
            ->setParameter('user', $user)
            ->setParameter('pokemon', $pokemon)
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return UserPokemon[]
     */
    public function findAllByUser(User $user): array
    {
        /** @var UserPokemon[] $results */
        $results = $this->createQueryBuilder('up')
            ->where('up.user = :user')
            ->setParameter('user', $user)
            ->orderBy('up.pokemon', 'ASC')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function countByUserAndVariant(User $user, string $variant): int
    {
        $field = match ($variant) {
            'normal' => 'hasNormal',
            'shiny' => 'hasShiny',
            'shadow' => 'hasShadow',
            'purified' => 'hasPurified',
            'lucky' => 'hasLucky',
            'xxl' => 'hasXxl',
            'xxs' => 'hasXxs',
            'perfect' => 'hasPerfect',
            default => throw new \InvalidArgumentException(sprintf('Invalid variant: %s', $variant)),
        };

        return (int) $this->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.user = :user')
            ->andWhere(sprintf('up.%s = :true', $field))
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctPokemonByUserAndVariant(User $user, string $variant): int
    {
        $field = match ($variant) {
            'normal' => 'hasNormal',
            'shiny' => 'hasShiny',
            'shadow' => 'hasShadow',
            'purified' => 'hasPurified',
            'lucky' => 'hasLucky',
            'xxl' => 'hasXxl',
            'xxs' => 'hasXxs',
            'perfect' => 'hasPerfect',
            default => throw new \InvalidArgumentException(sprintf('Invalid variant: %s', $variant)),
        };

        return (int) $this->createQueryBuilder('up')
            ->select('COUNT(DISTINCT p.number)')
            ->join('up.pokemon', 'p')
            ->where('up.user = :user')
            ->andWhere(sprintf('up.%s = :true', $field))
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPokemonWithAtLeastOneVariant(User $user): int
    {
        return (int) $this->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.user = :user')
            ->andWhere('(up.hasNormal = :true OR up.hasShiny = :true OR up.hasShadow = :true OR up.hasPurified = :true OR up.hasLucky = :true OR up.hasXxl = :true OR up.hasXxs = :true OR up.hasPerfect = :true)')
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPokemonWithAllVariants(User $user): int
    {
        return (int) $this->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.user = :user')
            ->andWhere('up.hasNormal = :true')
            ->andWhere('up.hasShiny = :true')
            ->andWhere('up.hasShadow = :true')
            ->andWhere('up.hasPurified = :true')
            ->andWhere('up.hasLucky = :true')
            ->andWhere('up.hasXxl = :true')
            ->andWhere('up.hasXxs = :true')
            ->andWhere('up.hasPerfect = :true')
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function countPokemonByGenerationAndVariant(User $user, string $variant): array
    {
        $field = match ($variant) {
            'normal' => 'hasNormal',
            'shiny' => 'hasShiny',
            'shadow' => 'hasShadow',
            'purified' => 'hasPurified',
            'lucky' => 'hasLucky',
            'xxl' => 'hasXxl',
            'xxs' => 'hasXxs',
            'perfect' => 'hasPerfect',
            default => throw new \InvalidArgumentException(sprintf('Invalid variant: %s', $variant)),
        };

        /** @var array<array{generation: string, total: string}> $results */
        $results = $this->createQueryBuilder('up')
            ->select('p.generation, COUNT(DISTINCT p.number) as total')
            ->join('up.pokemon', 'p')
            ->where('up.user = :user')
            ->andWhere(sprintf('up.%s = :true', $field))
            ->groupBy('p.generation')
            ->orderBy('p.generation', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('true', true)
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['generation']] = (int) $result['total'];
        }

        return $stats;
    }
}
