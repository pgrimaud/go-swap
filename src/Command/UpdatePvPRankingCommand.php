<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PvPRanking;
use App\Entity\PvPVersion;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\PvPRankingRepository;
use App\Repository\PvPVersionRepository;
use App\Service\GameMasterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update:pvp-ranking',
    description: 'Update PvP ranking data from PvPoke',
)]
class UpdatePvPRankingCommand extends Command
{
    public function __construct(
        private readonly GameMasterService $gameMasterService,
        private readonly PvPVersionRepository $pvpVersionRepository,
        private readonly PvPRankingRepository $pvpRankingRepository,
        private readonly PokemonRepository $pokemonRepository,
        private readonly MoveRepository $moveRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force reimport even if version exists')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $versionName = $this->gameMasterService->getPvPokeVersion();

            if ($versionName === null) {
                $io->error('Unable to retrieve PvPoke version');

                return Command::FAILURE;
            }

            $io->info(sprintf('Found PvPoke version: %s', $versionName));

            $existingVersion = $this->pvpVersionRepository->findOneByName($versionName);
            $force = $input->getOption('force');

            if ($existingVersion && !$force) {
                $io->warning(sprintf('Version %s already exists in database. Use --force to reimport.', $versionName));

                return Command::SUCCESS;
            }

            if (!$existingVersion) {
                $pvpVersion = new PvPVersion();
                $pvpVersion->setName($versionName);
                $this->entityManager->persist($pvpVersion);
                $this->entityManager->flush();
                $io->success(sprintf('Created PvP version: %s', $versionName));
            } else {
                $pvpVersion = $existingVersion;
                $io->info('Reimporting rankings for existing version...');
            }

            $leagues = [
                PvPRanking::LEAGUE_GREAT,
                PvPRanking::LEAGUE_ULTRA,
                PvPRanking::LEAGUE_MASTER,
            ];

            foreach ($leagues as $league) {
                $io->section(sprintf('Importing %s League rankings...', ucfirst($league)));
                $count = $this->importLeagueRankings($pvpVersion, $league, $versionName, $io);
                $io->success(sprintf('Imported %d rankings for %s League', $count, ucfirst($league)));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error: %s', $e->getMessage()));

            return Command::FAILURE;
        }
    }

    private function importLeagueRankings(PvPVersion $pvpVersion, string $league, string $version, SymfonyStyle $io): int
    {
        $rankings = $this->gameMasterService->getRankings($league, $version);
        $count = 0;
        $skipped = 0;

        foreach ($rankings as $rank => $data) {
            $speciesId = $data['speciesId'];

            $pokemon = $this->pokemonRepository->findOneBy(['slug' => $speciesId]);

            if (!$pokemon) {
                ++$skipped;
                continue;
            }

            $existingRanking = $this->pvpRankingRepository->findOneBy([
                'pvpVersion' => $pvpVersion,
                'pokemon' => $pokemon,
                'league' => $league,
            ]);

            if ($existingRanking) {
                $ranking = $existingRanking;
            } else {
                $ranking = new PvPRanking();
                $ranking->setPvpVersion($pvpVersion);
                $ranking->setPokemon($pokemon);
                $ranking->setLeague($league);
            }

            $ranking->setRank($rank + 1);
            $ranking->setScore((string) $data['score']);

            if (count($data['moveset']) === 3) {
                $fastMove = $this->moveRepository->findOneBy(['slug' => strtolower($data['moveset'][0])]);
                $chargedMove1 = $this->moveRepository->findOneBy(['slug' => strtolower($data['moveset'][1])]);
                $chargedMove2 = $this->moveRepository->findOneBy(['slug' => strtolower($data['moveset'][2])]);

                $ranking->setFastMove($fastMove);
                $ranking->setChargedMove1($chargedMove1);
                $ranking->setChargedMove2($chargedMove2);
            }

            $this->entityManager->persist($ranking);
            ++$count;

            if ($count % 100 === 0) {
                $this->entityManager->flush();
                $io->text(sprintf('  Processed %d rankings...', $count));
            }
        }

        $this->entityManager->flush();

        if ($skipped > 0) {
            $io->warning(sprintf('Skipped %d Pokémon not found in database', $skipped));
        }

        return $count;
    }
}
