<?php

namespace App\Repository;

use App\Entity\UserPokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<UserPokemon>
 *
 * @method UserPokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPokemon[]    findAll()
 * @method UserPokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPokemon::class);
    }

    public function save(UserPokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserPokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countByPokedex(?UserInterface $user, string $type): int
    {
        /** @var int $result */
        $result = $this->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.user = :user')
            ->andWhere('up.' . $type . ' = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }
}
