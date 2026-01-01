<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CustomList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<CustomList>
 */
class CustomListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomList::class);
    }

    public function findByUid(Uuid|string $uid): ?CustomList
    {
        if (is_string($uid)) {
            $uid = Uuid::fromString($uid);
        }

        /** @var CustomList|null $result */
        $result = $this->createQueryBuilder('cl')
            ->where('cl.uid = :uid')
            ->setParameter('uid', $uid, 'uuid')
            ->getQuery()
            ->getOneOrNullResult();

        return $result;
    }

    /**
     * @return CustomList[]
     */
    public function findAllByUser(User $user): array
    {
        /** @var CustomList[] $results */
        $results = $this->createQueryBuilder('cl')
            ->where('cl.user = :user')
            ->setParameter('user', $user)
            ->orderBy('cl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $results;
    }

    /**
     * @return CustomList[]
     */
    public function findPublicLists(int $limit = 10): array
    {
        /** @var CustomList[] $results */
        $results = $this->createQueryBuilder('cl')
            ->where('cl.isPublic = :true')
            ->setParameter('true', true)
            ->orderBy('cl.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('cl')
            ->select('COUNT(cl.id)')
            ->where('cl.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
