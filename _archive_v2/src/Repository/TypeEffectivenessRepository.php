<?php

namespace App\Repository;

use App\Entity\TypeEffectiveness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeEffectiveness>
 */
class TypeEffectivenessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeEffectiveness::class);
    }
}
