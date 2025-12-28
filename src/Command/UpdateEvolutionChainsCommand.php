<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\EvolutionChain;
use App\Repository\EvolutionChainRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:update:evolution-chains',
    description: 'Create evolution chain entities and link Pokemon from PokeAPI',
)]
final class UpdateEvolutionChainsCommand extends Command
{
    private const string POKEAPI_BASE_URL = 'https://pokeapi.co/api/v2';
    private const int MAX_POKEMON_ID = 1025;

    /** @var array<int, EvolutionChain> */
    private array $evolutionChainCache = [];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly PokemonRepository $pokemonRepository,
        private readonly EvolutionChainRepository $evolutionChainRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating evolution chains and linking Pokemon');

        $updated = 0;
        $errors = 0;
        $chainsCreated = 0;

        $io->progressStart(self::MAX_POKEMON_ID);

        for ($pokemonId = 1; $pokemonId <= self::MAX_POKEMON_ID; ++$pokemonId) {
            try {
                $chainId = $this->fetchEvolutionChainId($pokemonId);

                if ($chainId !== null) {
                    // Check if chain exists before creating
                    $chainExisted = $this->evolutionChainRepository->findOneBy(['chainId' => $chainId]) !== null
                        || isset($this->evolutionChainCache[$chainId]);

                    $evolutionChain = $this->getOrCreateEvolutionChain($chainId);

                    if (!$chainExisted) {
                        ++$chainsCreated;
                    }

                    $this->linkPokemonToChain($pokemonId, $evolutionChain);
                    ++$updated;
                }
            } catch (\Exception $e) {
                ++$errors;
                $io->warning(sprintf('Error for Pokemon #%d: %s', $pokemonId, $e->getMessage()));
            }

            $io->progressAdvance();

            // Flush every 50 Pokemon to avoid memory issues
            if ($pokemonId % 50 === 0) {
                $this->entityManager->flush();
            }
        }

        // Final flush
        $this->entityManager->flush();

        $io->progressFinish();

        $io->success(sprintf(
            'Evolution chains created! %d evolution chains created, %d Pokemon linked, %d errors.',
            $chainsCreated,
            $updated,
            $errors
        ));

        return Command::SUCCESS;
    }

    private function fetchEvolutionChainId(int $pokemonId): ?int
    {
        $url = sprintf('%s/pokemon-species/%d/', self::POKEAPI_BASE_URL, $pokemonId);
        $response = $this->httpClient->request('GET', $url);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = $response->toArray();

        if (!isset($data['evolution_chain']) || !is_array($data['evolution_chain']) || !isset($data['evolution_chain']['url'])) {
            return null;
        }

        // Extract evolution chain ID from URL: https://pokeapi.co/api/v2/evolution-chain/10/
        $evolutionChainUrl = $data['evolution_chain']['url'];
        if (!is_string($evolutionChainUrl)) {
            return null;
        }

        if (preg_match('/evolution-chain\/(\d+)/', $evolutionChainUrl, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function getOrCreateEvolutionChain(int $chainId): EvolutionChain
    {
        // Check cache first
        if (isset($this->evolutionChainCache[$chainId])) {
            return $this->evolutionChainCache[$chainId];
        }

        // Check database
        $evolutionChain = $this->evolutionChainRepository->findOneBy(['chainId' => $chainId]);

        $isNew = $evolutionChain === null;

        if ($isNew) {
            // Fetch chain details from PokeAPI to get base Pokemon name
            $basePokemonName = $this->fetchBasePokemonName($chainId);

            // Create new
            $evolutionChain = new EvolutionChain();
            $evolutionChain->setChainId($chainId);
            $evolutionChain->setBasePokemonName($basePokemonName);
            $this->entityManager->persist($evolutionChain);
            // Flush immediately to get the ID
            $this->entityManager->flush();
        }

        // Cache it
        $this->evolutionChainCache[$chainId] = $evolutionChain;

        return $evolutionChain;
    }

    private function fetchBasePokemonName(int $chainId): ?string
    {
        try {
            $url = sprintf('%s/evolution-chain/%d/', self::POKEAPI_BASE_URL, $chainId);
            $response = $this->httpClient->request('GET', $url);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = $response->toArray();

            // Navigate to the base species (first evolution)
            if (!isset($data['chain']) || !is_array($data['chain'])) {
                return null;
            }

            $chain = $data['chain'];
            if (!isset($chain['species']) || !is_array($chain['species'])) {
                return null;
            }

            $species = $chain['species'];
            if (!isset($species['name']) || !is_string($species['name'])) {
                return null;
            }

            return ucfirst($species['name']);
        } catch (\Exception) {
            return null;
        }
    }

    private function linkPokemonToChain(int $number, EvolutionChain $evolutionChain): void
    {
        $pokemon = $this->pokemonRepository->findBy(['number' => $number]);

        foreach ($pokemon as $poke) {
            $poke->setEvolutionChain($evolutionChain);
        }
    }
}
