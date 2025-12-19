<?php

namespace App\Command;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use App\Helper\GenerationHelper;
use App\Helper\HashHelper;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:pokemon',
    description: 'Update all pokémon data',
)]
class UpdatePokemonCommand extends AbstractSuggestCommand
{
    /** @var array<Type> */
    private array $types = [];

    /** @var array<Move> */
    private array $moves = [];

    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TypeRepository $typeRepository,
        private readonly MoveRepository $moveRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Updating pokémon data...');

        foreach ($this->gameMasterService->getPokemons() as $pokemon) {
            // avoid unreleased pokémon
            if (false === $pokemon['released'] && !in_array($pokemon['speciesId'], [
                'spewpa', 'ditto', 'shedinja', 'mudbray', 'mudsdale', 'cosmog', 'cosmoem',
            ])) {
                continue;
            }

            // avoid shadow, mega and special forms
            if (
                str_contains($pokemon['speciesId'], '_shadow')
                || str_contains($pokemon['speciesId'], '_mega')
                || str_contains($pokemon['speciesId'], 'pikachu_')
                || str_contains($pokemon['speciesId'], '_primal')
                || in_array('duplicate', $pokemon['tags'] ?? [])
            ) {
                continue;
            }

            $slug = $pokemon['speciesId'];

            $pokemonEntity = $this->pokemonRepository->findOneBy(['slug' => $slug]);

            if (!$pokemonEntity instanceof Pokemon || $pokemonEntity->getHash() !== HashHelper::fromPokemon($pokemon)) {
                $pokemonEntity = $pokemonEntity ?? new Pokemon();
                $pokemonEntity->setNumber($pokemon['dex']);
                $pokemonEntity->setName($pokemon['speciesName']);
                $pokemonEntity->setSlug($pokemon['speciesId']);
                $pokemonEntity->setAttack($pokemon['baseStats']['atk']);
                $pokemonEntity->setDefense($pokemon['baseStats']['def']);
                $pokemonEntity->setStamina($pokemon['baseStats']['hp']);
                $pokemonEntity->setShadow(in_array('shadoweligible', $pokemon['tags'] ?? []));
                $pokemonEntity->setGeneration(GenerationHelper::get($pokemon['dex']));
                $pokemonEntity->setHash(HashHelper::fromPokemon($pokemon));

                // manage types
                foreach ($pokemon['types'] as $type) {
                    if ('none' === $type) {
                        continue;
                    }
                    $pokemonEntity->addType($this->getType($io, $type, $pokemon['speciesName']));
                }

                // manage fast moves
                foreach (array_merge($pokemon['fastMoves'], $pokemon['chargedMoves']) as $move) {
                    // check if move is already linked to the Pokémon
                    $existingMove = $pokemonEntity->getPokemonMoves()->filter(
                        fn (PokemonMove $pokemonMove) => $pokemonMove->getMove()?->getSlug() === mb_strtolower($move)
                    )->first();

                    if ($existingMove) {
                        $existingMove->setElite(in_array($move, $pokemon['eliteMoves'] ?? []));
                        continue;
                    }

                    $moveEntity = $this->getMove($io, mb_strtolower($move), $pokemon['speciesName']);

                    $pokemonMove = new PokemonMove();
                    $pokemonMove->setMove($moveEntity);
                    $pokemonMove->setPokemon($pokemonEntity);
                    $pokemonMove->setElite(in_array($move, $pokemon['eliteMoves'] ?? []));

                    $pokemonEntity->addPokemonMove($pokemonMove);
                }

                $this->entityManager->persist($pokemonEntity);
                $this->entityManager->flush();
            }
        }

        $io->success('Pokémon data updated successfully.');

        return Command::SUCCESS;
    }

    private function getType(SymfonyStyle $io, string $typeAsString, string $pokemonName): Type
    {
        if (array_key_exists($typeAsString, $this->types)) {
            return $this->types[$typeAsString];
        }

        $type = $this->typeRepository->findOneBy(['slug' => $typeAsString]);

        if (!$type instanceof Type) {
            $io->error(sprintf(
                'Type not found in %s : %s',
                $pokemonName,
                $typeAsString
            ));
            $this->runParentCommand($io, 'app:update:types');

            return $this->getType($io, $typeAsString, $pokemonName);
        }

        return $type;
    }

    private function getMove(SymfonyStyle $io, string $moveAsString, string $pokemonName): Move
    {
        if (array_key_exists($moveAsString, $this->moves)) {
            return $this->moves[$moveAsString];
        }

        $move = $this->moveRepository->findOneBy(['slug' => $moveAsString]);

        if (!$move instanceof Move) {
            $io->error(sprintf(
                'Type not found in %s : %s',
                $pokemonName,
                $moveAsString
            ));
            $this->runParentCommand($io, 'app:update:moves');

            return $this->getMove($io, $moveAsString, $pokemonName);
        }

        return $move;
    }
}
