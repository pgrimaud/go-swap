<?php

namespace App\Command;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-shadow',
    description: 'Import all shadow & purified pokemon from API.',
)]
class ImportShadowCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $response = $this->httpClient->request('GET', 'https://api.gofieldguide.app/shadow/get-shadow-ids')->toArray();

        foreach ($response['data'] as $pokemon) {
            $pokemonEntity = $this->pokemonRepository->getPokemonByName(strtolower($pokemon['pokemon_id']));

            if ($pokemonEntity instanceof Pokemon &&
                ($pokemonEntity->getIsShadow() === false || $pokemonEntity->getIsPurified() === false)
            ) {
                $pokemonEntity->setIsShadow(true);
                $pokemonEntity->setIsPurified(true);
                $this->entityManager->persist($pokemonEntity);
            }
        }

        $this->entityManager->flush();

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
