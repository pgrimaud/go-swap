<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPvPPokemon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPvPPokemon>
 */
class UserPvPPokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPvPPokemon::class);
    }

    /**
     * @return UserPvPPokemon[]
     */
    public function findForUserOrderedByNumber(User $user, ?int $minRank = null, ?int $maxRank = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->join('p.pokemon', 'pokemon')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pokemon.number', 'ASC')
            ->addOrderBy('p.leagueRank', 'ASC');

        if (null !== $minRank && null !== $maxRank) {
            $queryBuilder->andWhere('p.leagueRank >= :minRank')->setParameter('minRank', $minRank);
            $queryBuilder->andWhere('p.leagueRank <= :maxRank')->setParameter('maxRank', $maxRank);
        }

        /** @var UserPvPPokemon[] $result */
        $result = $queryBuilder->getQuery()
            ->getResult();

        return $result;
    }
}
