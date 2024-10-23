<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPokemon;
use App\Helper\GenerationHelper;
use App\Helper\PokedexHelper;
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
            ->select('COUNT(DISTINCT(p.number))')
            ->join('up.pokemon', 'p')
            ->where('up.user = :user')
            ->andWhere('up.' . $type . ' = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function countByGeneration(?UserInterface $user, string $type): array
    {
        if (!$user instanceof User) {
            return [];
        }

        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(DISTINCT(p.number)) as total, p.generation
            FROM pokemon p 
            RIGHT JOIN user_pokemon up ON up.pokemon_id = p.id
            RIGHT JOIN user u ON u.id = up.user_id
            WHERE u.id = :userId
            AND up.' . PokedexHelper::POKEDEX_MAPPING_FIELD[$type] . ' = 1
            GROUP BY p.generation
            ORDER BY generation';

        $stmt = $connection->prepare($sql);

        $statement = $stmt->executeQuery([
            'userId' => $user->getId(),
        ]);

        $results = $statement->fetchAllAssociative();

        $allGenerations = GenerationHelper::getAllGenerations();

        $allGenerationInit = [];

        foreach ($allGenerations as $generationCode => $generationName) {
            $allGenerationInit[$generationCode] = [
                'total' => 0,
                'generation' => $generationCode,
                'name' => $generationName
            ];
        }

        foreach ($results as $result) {
            $allGenerationInit[$result['generation']] = [
                'total' => $result['total'],
                'generation' => $result['generation'],
                'name' => GenerationHelper::getAllGenerations()[$result['generation']]
            ];
        }

        return $allGenerationInit;
    }
}
