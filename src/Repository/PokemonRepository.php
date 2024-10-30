<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Helper\PokedexHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Pokemon>
 *
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function save(Pokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pokemon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUserPokemon(User|UserInterface|null $user): array
    {
        return $this->createQueryBuilder('p', 'p.id')
            ->select('p.id, p.number', 'up.shiny', 'up.normal', 'up.lucky', 'up.threeStars', 'up.numberShiny', 'up.shadow', 'up.purified', 'up.shinyThreeStars', 'up.perfect')
            ->join('p.userPokemon', 'up')
            ->andWhere('up.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.number', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function getCountByGeneration(string $generation): int
    {
        /** @var int $result */
        $result = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT(p.number))')
            ->where('p.generation = :generation')
            ->setParameter('generation', $generation)
            ->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function countUnique(string $type): int
    {
        $query = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT(p.number))');

        if (in_array($type, PokedexHelper::FILTERABLE_TYPES)) {
            $query->andWhere('p.is' . ucfirst($type) . ' = true');
        }

        /** @var int $result */
        $result = $query->getQuery()
            ->getSingleScalarResult();

        return $result;
    }

    public function missingShinyPokemons(User $userId1, User $userId2): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT p.*,shiny_doubles.number_shiny
        FROM (
            SELECT up1.pokemon_id,up1.number_shiny
            FROM user_pokemon up1
            WHERE up1.user_id = :userId1 AND up1.shiny = 1 AND up1.number_shiny > 1
        ) AS shiny_doubles
        LEFT JOIN (
            SELECT up2.pokemon_id
            FROM user_pokemon up2
            WHERE up2.user_id = :userId2 AND up2.shiny = 1
        ) AS user2_pokemon ON shiny_doubles.pokemon_id = user2_pokemon.pokemon_id
        INNER JOIN pokemon p ON shiny_doubles.pokemon_id = p.id
        WHERE p.is_shiny = 1 AND user2_pokemon.pokemon_id IS NULL;
        ';

        $stmt = $connection->prepare($sql);
        $statement = $stmt->executeQuery([
            'userId1' => $userId1->getId(),
            'userId2' => $userId2->getId(),
        ]);

        return $statement->fetchAllAssociative();
    }

    public function missingShinyPokemonEvolution(User $userId1, User $userId2): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT  p.*,up.number_shiny
        FROM pokemon p
        LEFT JOIN user_pokemon up ON p.id = up.pokemon_id AND up.user_id = :userId1
        LEFT JOIN evolution_chain ec ON p.evolution_chain_id = ec.id
        WHERE p.is_shiny = 1
        AND up.shiny = 1
        AND up.number_shiny > 1
        AND ec.id IN (
            SELECT ec.id
            FROM pokemon p
            LEFT JOIN user_pokemon up ON p.id = up.pokemon_id AND up.user_id = :userId2
            LEFT JOIN evolution_chain ec ON p.evolution_chain_id = ec.id
            WHERE p.is_shiny = 1
            AND (up.shiny = 0 OR up.shiny IS NULL)
            AND ec.id IS NOT NULL
            GROUP BY ec.id
        );
        ';

        $stmt = $connection->prepare($sql);
        $statement = $stmt->executeQuery([
            'userId1' => $userId1->getId(),
            'userId2' => $userId2->getId(),
        ]);

        return $statement->fetchAllAssociative();
    }

    public function getEvolutionsChains(): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT number, french_name, english_name, evolution_chain_id
            FROM pokemon
        ';

        $stmt = $connection->prepare($sql);
        $statement = $stmt->executeQuery();

        $evolutionsChains = [];

        foreach ($statement->fetchAllAssociative() as $pokemon) {
            $evolutionsChains[$pokemon['evolution_chain_id']][] = $pokemon;
        }

        return $evolutionsChains;
    }

    public function getPokemonByName(string $name): ?Pokemon
    {
        $result = $this->createQueryBuilder('p')
            ->where('p.englishName LIKE :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof Pokemon ? $result : null;
    }

    public function missingPokemons(int $userId, string $type): string
    {
        $field = PokedexHelper::POKEDEX_MAPPING_FIELD[$type];

        $connection = $this->getEntityManager()->getConnection();

        $sql = sprintf('
            SELECT p.number
            FROM pokemon p
            LEFT JOIN user_pokemon up ON up.pokemon_id = p.id
            WHERE up.user_id = :userId
            AND up.%s = 0
        ', $field);

        if (in_array($type, PokedexHelper::FILTERABLE_TYPES)) {
            $sql .= sprintf(' AND p.is_%s = 1', $field);
        }

        $stmt = $connection->prepare($sql);
        $statement = $stmt->executeQuery([
            'userId' => $userId,
        ]);

        $results = $statement->fetchAllNumeric();

        $filter = array_map(function ($result) {
            return $result[0];
        }, $results);

        sort($filter);

        return implode(',', $filter);
    }
}
