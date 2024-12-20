<?php

namespace App\Command;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-moves',
    description: 'Import pokemons moves',
)]
class ImportMovesCommand extends Command
{
    private const ALLOWED_NORMAL_POKEMON = [862, 863, 865, 866, 867];
    private array $types;
    private SymfonyStyle $io;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeRepository $typeRepository,
        private readonly MoveRepository $moveRepository,
        private readonly PokemonRepository $pokemonRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->truncateTable();

        $this->types = $this->typeRepository->findAll();

        $pokemons = $this->pokemonRepository->findAll();

        foreach ($pokemons as $pokemon) {
            $request = $this->client->request(
                'GET',
                sprintf('https://pokemon-go-api.github.io/pokemon-go-api/api/pokedex/id/%s.json', $pokemon->getNumber())
            );
            $response = $request->toArray();

            if ($pokemon->getForm() === 'Normal' || in_array($pokemon->getNumber(), self::ALLOWED_NORMAL_POKEMON)) {
                $quickMoves = $response['quickMoves'];
                $chargedMoves = $response['cinematicMoves'];

                $eliteQuickMoves = $response['eliteQuickMoves'];
                $eliteChargedMoves = $response['eliteCinematicMoves'];
            } else {
                $keyName = strtoupper(sprintf(
                    '%s_%s',
                    $pokemon->getSlug(),
                    $pokemon->getForm()
                ));

                if (!isset($response['regionForms'][$keyName])) {
                    throw new \Exception('Form not found: ' . $keyName);
                }

                $quickMoves = $response['regionForms'][$keyName]['quickMoves'];
                $chargedMoves = $response['regionForms'][$keyName]['cinematicMoves'];

                $eliteQuickMoves = $response['regionForms'][$keyName]['eliteQuickMoves'];
                $eliteChargedMoves = $response['regionForms'][$keyName]['eliteCinematicMoves'];
            }

            $this->importMoves($pokemon, $quickMoves, Move::FAST_MOVE);
            $this->importMoves($pokemon, $chargedMoves, Move::CHARGED_MOVE);
            $this->importMoves($pokemon, $eliteQuickMoves, Move::CHARGED_MOVE, true);
            $this->importMoves($pokemon, $eliteChargedMoves, Move::CHARGED_MOVE, true);

            $this->io->writeln(sprintf('Moves imported for %s', $pokemon->getEnglishName()));
        }

        $this->io->success('Moves imported');

        return Command::SUCCESS;
    }

    public function truncateTable(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('pokemon_move', true));
    }

    private function getMoveOrCreate(array $move, string $type): Move
    {
        $moveEntity = $this->moveRepository->findOneBy(['name' => $move['names']['English']]);

        if (!$moveEntity instanceof Move) {
            $moveEntity = new Move();
            $moveEntity->setName($move['names']['English']);
            $moveEntity->setType($this->getType($move['type']['names']['English']));
            $moveEntity->setPower($move['combat']['power']);
            $moveEntity->setEnergyDelta($move['combat']['energy']);
            $moveEntity->setAttackType($type);
            $moveEntity->setTurnDuration($move['combat']['turns']);

            $this->entityManager->persist($moveEntity);
            $this->entityManager->flush();
        }

        // for future updates
        if ($moveEntity->getPower() !== $move['combat']['power']) {
            throw new \Exception('Power is different for move: ' . $move['names']['English']);
        }

        if ($moveEntity->getEnergyDelta() !== $move['combat']['energy']) {
            throw new \Exception('Energy is different for move: ' . $move['names']['English']);
        }

        if ($moveEntity->getTurnDuration() != $move['combat']['turns']) {
            throw new \Exception('Turn duration is different for move: ' . $move['names']['English']);
        }

        return $moveEntity;
    }

    private function getType(string $typeName): Type
    {
        foreach ($this->types as $type) {
            if ($type->getName() === $typeName) {
                return $type;
            }
        }

        $this->io->error('Type not found: ' . $typeName);

        throw new \Exception('Type not found: ' . $typeName);
    }

    private function importMoves(Pokemon $pokemon, array $moves, string $type, bool $isElite = false): void
    {
        foreach ($moves as $quickMove) {
            $pokemonMove = new PokemonMove();
            $pokemonMove->setPokemon($pokemon);
            $pokemonMove->setMove($this->getMoveOrCreate($quickMove, $type));
            $pokemonMove->setElite($isElite);

            $this->entityManager->persist($pokemonMove);
        }

        $this->entityManager->flush();
    }
}
