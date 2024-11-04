<?php

namespace App\Command;

use App\Entity\EvolutionChain;
use App\Repository\EvolutionChainRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-evolution-chains',
    description: 'Import evolution chains to database',
)]
class ImportEvolutionChainsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface   $entityManager,
        private readonly PokemonRepository        $pokemonRepository,
        private readonly EvolutionChainRepository $evolutionChainRepository,
        private readonly HttpClientInterface      $httpClient
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $response = $this->httpClient->request('GET',
            'https://pokeapi.co/api/v2/evolution-chain'
        );

        $indexResult = $response->toArray();

        $progressBar = new ProgressBar($io, $indexResult['count']);
        $progressBar->start();

        for ($i = 1; $i <= $indexResult['count']; $i++) {

            $progressBar->advance();

            try {
                $response = $this->httpClient->request('GET',
                    'https://pokeapi.co/api/v2/evolution-chain/' . $i
                );

                $result = $response->toArray();
            } catch (ClientExceptionInterface $exception) {
                if ($exception->getCode() === 404) {
                    continue;
                }

                $io->error('An error occurred: ' . $exception->getMessage());
                return Command::FAILURE;
            }

            // fetch evolution chain
            $evolutionChain = $this->evolutionChainRepository->findOneBy([
                'apiId' => $i
            ]);

            // getPokemons of chain
            $pokemons = $this->getPokemons($result['chain']);

            /** @var array $firstPokemons */
            $firstPokemons = $this->pokemonRepository->findBy([
                'number' => $pokemons[0]
            ]);

            // pokémon isn't in Pokémon Go or has no evolution
            if (empty($firstPokemons) || count($pokemons) === 0) {
                continue;
            }

            if (!$evolutionChain) {
                $evolutionChain = new EvolutionChain();
                $evolutionChain->setApiId($i);
                $evolutionChain->setName((string)$firstPokemons[0]->getFrenchName());
                array_map(fn($pokemon) => $evolutionChain->addPokemon($pokemon), $firstPokemons);
            } else {
                $evolutionChain->removeAllPokemons();
                array_map(fn($pokemon) => $evolutionChain->addPokemon($pokemon), $firstPokemons);
            }

            array_shift($pokemons);
            foreach ($pokemons as $pokemon) {
                /** @var array $pokemonEntities */
                $pokemonEntities = $this->pokemonRepository->findBy([
                    'number' => $pokemon
                ]);

                if (count($pokemonEntities) > 0) {
                    array_map(fn($pokemonEntity) => $evolutionChain->addPokemon($pokemonEntity), $pokemonEntities);
                }
            }

            $this->entityManager->persist($evolutionChain);

            $evolutions = $this->extractEvolution($result['chain']);


            foreach ($evolutions as $k => $evolution) {
                $pokemon = $this->pokemonRepository->findOneBy([
                    'number' => $evolution
                ]);

                // probably a pokémon that isn't in Pokémon Go yet
                if ($pokemon === null) {
                    continue;
                }

                if ($pokemon->getEvolutionChainPosition() === null) {
                    $pokemon->setEvolutionChainPosition($k + 1);
                    $this->entityManager->persist($pokemon);
                }
            }

            $this->entityManager->flush();
        }

        $progressBar->finish();

        $io->success('Done!');

        return Command::SUCCESS;
    }

    private function getIdFromUrl(string $url): int
    {
        return (int)explode('/', $url)[6];
    }

    private function getPokemons(array $apiResult): array
    {
        $pokemons = [];

        $pokemons[] = $this->getIdFromUrl($apiResult['species']['url']);

        foreach ($apiResult['evolves_to'] as $evolvesTo) {
            $pokemons = array_merge($pokemons, $this->getPokemons($evolvesTo));
        }

        return $pokemons;
    }

    private function extractEvolution(array $evolution, array $evolutions = []): array
    {
        preg_match('/pokemon-species\/(\d+)\//', $evolution['species']['url'], $matches);

        if(!empty($matches[1])) {
            $evolutions[] = (int)$matches[1];
            if (!empty($evolution['evolves_to'])) {
                foreach ($evolution['evolves_to'] as $nextEvolution) {
                    return $this->extractEvolution($nextEvolution, $evolutions);
                }
            }
        }

        return $evolutions;
    }
}
