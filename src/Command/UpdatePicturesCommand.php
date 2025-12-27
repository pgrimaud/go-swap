<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Service\PokemonImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:pictures',
    description: 'Download and resize all Pokémon pictures',
)]
final class UpdatePicturesCommand extends Command
{
    private const int BATCH_SIZE = 20;

    public function __construct(
        private readonly PokemonRepository $pokemonRepository,
        private readonly PokemonImageService $imageService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('');
        $io->writeln('<fg=cyan>═══════════════════════════════════════</>');
        $io->writeln('<fg=cyan;options=bold>  📸 Updating Pictures</>');
        $io->writeln('<fg=cyan>═══════════════════════════════════════</>');
        $io->writeln('');

        // Download normal pictures
        $normalPokemon = $this->pokemonRepository->findAllWithoutPictures();
        $io->section('Normal Pictures');

        if (0 === count($normalPokemon)) {
            $io->writeln('All Pokémon already have normal pictures.');
        } else {
            $io->writeln(sprintf('Found %d Pokémon without normal pictures.', count($normalPokemon)));
            $this->downloadPictures($normalPokemon, $io, false);
        }

        // Download shiny pictures
        $shinyPokemon = $this->pokemonRepository->findAllShinyWithoutPictures();
        $io->newLine();
        $io->section('Shiny Pictures');

        if (0 === count($shinyPokemon)) {
            $io->writeln('All shiny Pokémon already have pictures.');
        } else {
            $io->writeln(sprintf('Found %d shiny Pokémon without pictures.', count($shinyPokemon)));
            $this->downloadPictures($shinyPokemon, $io, true);
        }

        $io->newLine();
        $io->success('Pictures update completed!');

        return Command::SUCCESS;
    }

    /**
     * @param Pokemon[] $pokemon
     */
    private function downloadPictures(array $pokemon, SymfonyStyle $io, bool $shiny): void
    {
        $io->newLine();
        $progressBar = $io->createProgressBar(count($pokemon));
        $progressBar->start();

        $processed = 0;
        $failed = 0;

        foreach ($pokemon as $pkmn) {
            $filename = $this->imageService->downloadAndSavePicture($pkmn, $io, $shiny);

            if (null !== $filename) {
                if ($shiny) {
                    $pkmn->setShinyPicture($filename);
                } else {
                    $pkmn->setPicture($filename);
                }
                ++$processed;
            } else {
                ++$failed;
            }

            if (0 === ($processed % self::BATCH_SIZE)) {
                $this->entityManager->flush();
            }

            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $io->newLine(2);

        $io->writeln(sprintf(
            '%s pictures: %d successful, %d failed.',
            $shiny ? 'Shiny' : 'Normal',
            $processed,
            $failed
        ));
    }
}
