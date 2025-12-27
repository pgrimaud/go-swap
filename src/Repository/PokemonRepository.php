<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pokemon;
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
}
