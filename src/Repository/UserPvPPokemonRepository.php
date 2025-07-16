<?php

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
    public function findForUserOrderedByNumber(User $user): array
    {
        /** @var UserPvPPokemon[] $result */
        $result = $this->createQueryBuilder('p')
            ->join('p.pokemon', 'pokemon')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pokemon.number', 'ASC')
            ->addOrderBy('p.leagueRank', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
