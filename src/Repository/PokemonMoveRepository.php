<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PokemonMove;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PokemonMove>
 */
class PokemonMoveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonMove::class);
    }
}
