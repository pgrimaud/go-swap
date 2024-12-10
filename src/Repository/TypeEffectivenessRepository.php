<?php

namespace App\Repository;

use App\Entity\Type;
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

    public function getWeakAgainst(): array
    {
        $results = [];

        /** @var TypeEffectiveness[] $queryResults */
        $queryResults = $this->createQueryBuilder('te')
            ->select('te')
            ->where('te.multiplier > 1')
            ->getQuery()
            ->getResult();

        foreach ($queryResults as $queryResult) {
            /** @var Type $targetType */
            $targetType = $queryResult->getTargetType();
            $results[$targetType->getId()][] = $queryResult;
        }

        return $results;
    }
}
