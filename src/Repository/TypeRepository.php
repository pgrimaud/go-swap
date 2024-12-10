<?php

namespace App\Repository;

use App\Entity\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Type>
 */
class TypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    public function getRandomType(): ?Type
    {
        $result = $this->createQueryBuilder('t')
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        return $result instanceof Type ? $result : null;
    }

    /**
     * @param array<Type> $correctAnswerTypes
     *
     * @return array<Type>
     */
    public function findRandomTypes(array $correctAnswerTypes, int $maxResults = 3): array
    {
        $query = $this->createQueryBuilder('t')
            ->where('t NOT IN (:correctAnswerTypes)')
            ->setParameter('correctAnswerTypes', $correctAnswerTypes)
            ->orderBy('RAND()')
            ->setMaxResults($maxResults)
            ->getQuery()->getResult();

        return is_array($query) ? $query : [];
    }
}
