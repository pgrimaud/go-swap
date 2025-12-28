<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pokemon;
use App\Helper\GenerationHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pokemon>
 */
class PokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    /**
     * @return Pokemon[]
     */
    public function findAllWithoutPictures(): array
    {
        /** @var Pokemon[] $results */
        $results = $this->createQueryBuilder('p')
            ->where('p.picture IS NULL')
            ->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * @return Pokemon[]
     */
    public function findAllShinyWithoutPictures(): array
    {
        /** @var Pokemon[] $results */
        $results = $this->createQueryBuilder('p')
            ->where('p.shinyPicture IS NULL')
            ->andWhere('p.shiny = :shiny')
            ->setParameter('shiny', true)
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function countTotalDistinctPokemon(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.number)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctShinies(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.number)')
            ->where('p.shiny = :shiny')
            ->setParameter('shiny', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctShadows(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.number)')
            ->where('p.shadow = :shadow')
            ->setParameter('shadow', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDistinctLuckies(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.number)')
            ->where('p.lucky = :lucky')
            ->setParameter('lucky', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<string, int>
     */
    public function countPokemonByGeneration(): array
    {
        /** @var array<array{generation: string, total: string}> $results */
        $results = $this->createQueryBuilder('p')
            ->select('p.generation, COUNT(DISTINCT p.number) as total')
            ->groupBy('p.generation')
            ->orderBy('p.generation', 'ASC')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['generation']] = (int) $result['total'];
        }

        return $this->sortByGenerationOrder($stats);
    }

    /**
     * Count distinct Pokemon by generation for a specific variant availability.
     *
     * @return array<string, int>
     */
    public function countPokemonByGenerationAndVariant(string $variant): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.generation, COUNT(DISTINCT p.number) as total')
            ->groupBy('p.generation')
            ->orderBy('p.generation', 'ASC');

        // Filter by variant availability
        if ($variant === 'shiny') {
            $qb->where('p.shiny = :shiny')
                ->setParameter('shiny', true);
        } elseif ($variant === 'shadow' || $variant === 'purified') {
            $qb->where('p.shadow = :shadow')
                ->setParameter('shadow', true);
        } elseif ($variant === 'lucky') {
            $qb->where('p.lucky = :lucky')
                ->setParameter('lucky', true);
        }
        // For normal, xxl, xxs, perfect: all Pokemon are available (no filter)

        /** @var array<array{generation: string, total: string}> $results */
        $results = $qb->getQuery()->getResult();

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
}
