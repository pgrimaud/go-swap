<?php

namespace App\Repository;

use App\Entity\EvolutionChain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvolutionChain>
 *
 * @method EvolutionChain|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvolutionChain|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvolutionChain[]    findAll()
 * @method EvolutionChain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvolutionChainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvolutionChain::class);
    }

    public function save(EvolutionChain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EvolutionChain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getEvolutionByApiId(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT p.number, p.evolution_chain_id, p.evolution_chain_position
            FROM pokemon p
            ORDER BY p.evolution_chain_position
        ';

        $stmt = $connection->prepare($sql);
        $statement = $stmt->executeQuery();

        $evolutionChains = [];

        array_map(function ($result) use (&$evolutionChains) {
            $evolutionChains[$result['evolution_chain_id']][] = $result['number'];
        }, $statement->fetchAllAssociative());

        return $evolutionChains;
    }
}
