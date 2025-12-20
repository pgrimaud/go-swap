<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PokemonRepository;
use App\Service\PokemonImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addOption(
            'strict',
            null,
            InputOption::VALUE_NONE,
            'Stop on first error (no fallback to Pokekalos)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Updating Pokémon Pictures');

        $pokemon = $this->pokemonRepository->findAllWithoutPictures();

        if (0 === count($pokemon)) {
            $io->success('All Pokémon already have pictures.');

            return Command::SUCCESS;
        }

        $io->note(sprintf('Found %d Pokémon without pictures', count($pokemon)));

        $strictMode = (bool) $input->getOption('strict');
        if ($strictMode) {
            $io->warning('Strict mode enabled: will stop on first error');
        }

        $progressBar = new ProgressBar($output, count($pokemon));
        $progressBar->start();

        $processed = 0;
        $failed = 0;

        foreach ($pokemon as $pkmn) {
            $filename = $this->imageService->downloadAndSavePicture($pkmn, $io, $strictMode);

            if (null !== $filename) {
                $pkmn->setPicture($filename);
                ++$processed;
            } else {
                ++$failed;

                if ($strictMode) {
                    $progressBar->finish();
                    $io->newLine(2);
                    $io->error(sprintf(
                        'Strict mode: Stopped after failing to download image for %s (#%d)',
                        $pkmn->getName(),
                        $pkmn->getNumber()
                    ));

                    return Command::FAILURE;
                }
            }

            if (0 === ($processed % self::BATCH_SIZE)) {
                $this->entityManager->flush();
            }

            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();

        $io->newLine(2);
        $io->success(sprintf(
            'Pictures imported: %d successful, %d failed',
            $processed,
            $failed
        ));

        return Command::SUCCESS;
    }
}
