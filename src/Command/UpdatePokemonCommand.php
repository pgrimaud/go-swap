<?php

namespace App\Command;

use App\Entity\Pokemon;
use App\Helper\HashHelper;
use App\Repository\PokemonRepository;
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
class UpdatePokemonCommand extends Command
{
    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Updating pokémon data...');

        foreach ($this->gameMasterService->getPokemons() as $pokemon) {
            // avoid unreleased
            if ($pokemon['released'] === false) {
                continue;
            }

            // avoid shadow && mega
            if (
                str_contains($pokemon['speciesId'], '_shadow')
                || str_contains($pokemon['speciesId'], '_mega')
                || str_contains($pokemon['speciesId'], 'mega')
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
                $pokemonEntity->setHash(HashHelper::fromPokemon($pokemon));

                /* @todo manage types & moves */

                $this->entityManager->persist($pokemonEntity);
                $this->entityManager->flush();
            }
        }

        return Command::SUCCESS;
    }
}
