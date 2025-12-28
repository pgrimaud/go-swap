<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserPokemon;
use App\Helper\GenerationHelper;
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

        return $this->sortByGenerationOrder($stats);
    }

    /**
     * @param array<string, int> $stats
     *
     * @return array<string, int>
     */
    private function sortByGenerationOrder(array $stats): array
    {
        $orderedStats = [];
        foreach (GenerationHelper::GENERATIONS as $generation) {
            if (isset($stats[$generation])) {
                $orderedStats[$generation] = $stats[$generation];
            }
        }

        return $orderedStats;
    }

    /**
     * Get all variant counts for a user in one query.
     *
     * @return array<string, int>
     */
    public function countAllVariantsByUser(User $user): array
    {
        /** @var array<array{variant: string, total: string}> $results */
        $results = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT 
                COUNT(DISTINCT CASE WHEN has_normal = 1 THEN p.number END) as normal,
                COUNT(DISTINCT CASE WHEN has_shiny = 1 THEN p.number END) as shiny,
                COUNT(DISTINCT CASE WHEN has_shadow = 1 THEN p.number END) as shadow,
                COUNT(DISTINCT CASE WHEN has_purified = 1 THEN p.number END) as purified,
                COUNT(DISTINCT CASE WHEN has_lucky = 1 THEN p.number END) as lucky,
                COUNT(DISTINCT CASE WHEN has_xxl = 1 THEN p.number END) as xxl,
                COUNT(DISTINCT CASE WHEN has_xxs = 1 THEN p.number END) as xxs,
                COUNT(DISTINCT CASE WHEN has_perfect = 1 THEN p.number END) as perfect
            FROM user_pokemon up
            JOIN pokemon p ON up.pokemon_id = p.id
            WHERE up.user_id = :user_id',
            ['user_id' => $user->getId()]
        )->fetchAssociative();

        return [
            'normal' => (int) ($results['normal'] ?? 0),
            'shiny' => (int) ($results['shiny'] ?? 0),
            'shadow' => (int) ($results['shadow'] ?? 0),
            'purified' => (int) ($results['purified'] ?? 0),
            'lucky' => (int) ($results['lucky'] ?? 0),
            'xxl' => (int) ($results['xxl'] ?? 0),
            'xxs' => (int) ($results['xxs'] ?? 0),
            'perfect' => (int) ($results['perfect'] ?? 0),
        ];
    }

    /**
     * Get all variant counts by generation for a user in one query.
     *
     * @return array<string, array<string, int>>
     */
    public function countAllVariantsByGenerationForUser(User $user): array
    {
        /** @var array<array{generation: string, normal: string, shiny: string, shadow: string, purified: string, lucky: string, xxl: string, xxs: string, perfect: string}> $results */
        $results = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT 
                p.generation,
                COUNT(DISTINCT CASE WHEN up.has_normal = 1 THEN p.number END) as normal,
                COUNT(DISTINCT CASE WHEN up.has_shiny = 1 THEN p.number END) as shiny,
                COUNT(DISTINCT CASE WHEN up.has_shadow = 1 THEN p.number END) as shadow,
                COUNT(DISTINCT CASE WHEN up.has_purified = 1 THEN p.number END) as purified,
                COUNT(DISTINCT CASE WHEN up.has_lucky = 1 THEN p.number END) as lucky,
                COUNT(DISTINCT CASE WHEN up.has_xxl = 1 THEN p.number END) as xxl,
                COUNT(DISTINCT CASE WHEN up.has_xxs = 1 THEN p.number END) as xxs,
                COUNT(DISTINCT CASE WHEN up.has_perfect = 1 THEN p.number END) as perfect
            FROM user_pokemon up
            JOIN pokemon p ON up.pokemon_id = p.id
            WHERE up.user_id = :user_id
            GROUP BY p.generation
            ORDER BY p.generation ASC',
            ['user_id' => $user->getId()]
        )->fetchAllAssociative();

        $stats = [];
        foreach ($results as $row) {
            $generation = $row['generation'];
            $stats['normal'][$generation] = (int) $row['normal'];
            $stats['shiny'][$generation] = (int) $row['shiny'];
            $stats['shadow'][$generation] = (int) $row['shadow'];
            $stats['purified'][$generation] = (int) $row['purified'];
            $stats['lucky'][$generation] = (int) $row['lucky'];
            $stats['xxl'][$generation] = (int) $row['xxl'];
            $stats['xxs'][$generation] = (int) $row['xxs'];
            $stats['perfect'][$generation] = (int) $row['perfect'];
        }

        // Sort each variant by generation order
        foreach (array_keys($stats) as $variant) {
            $stats[$variant] = $this->sortByGenerationOrder($stats[$variant]);
        }

        return $stats;
    }
}
