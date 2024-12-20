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

    public function getStrongAgainst(Type $type): mixed
    {
        return $this->createQueryBuilder('te')
            ->select('te, t')
            ->join('te.targetType', 't')
            ->where('te.multiplier > 1')
            ->andWhere('te.sourceType = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    public function getVulnerableTo(Type $type): mixed
    {
        return $this->createQueryBuilder('te')
            ->select('te, t')
            ->join('te.sourceType', 't')
            ->where('te.multiplier > 1')
            ->andWhere('te.targetType = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    public function getResistantTo(Type $type): mixed
    {
        return $this->createQueryBuilder('te')
            ->select('te, t')
            ->join('te.sourceType', 't')
            ->where('te.multiplier < 1')
            ->andWhere('te.targetType = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    public function getNotEffectiveAgainst(Type $type): mixed
    {
        return $this->createQueryBuilder('te')
            ->select('te, t')
            ->join('te.targetType', 't')
            ->where('te.multiplier < 1')
            ->andWhere('te.sourceType = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}
