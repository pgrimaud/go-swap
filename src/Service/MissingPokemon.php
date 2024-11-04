<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\EvolutionChainRepository;
use App\Repository\PokemonRepository;

class MissingPokemon
{
    public function __construct(
        private PokemonRepository        $pokemonRepository,
        private EvolutionChainRepository $evolutionChainRepository
    )
    {
    }

    public function getMissingByDex(int $userId, string $type): string
    {
        $evolutionChains = $this->evolutionChainRepository->getEvolutionByApiId();
        $results = $this->pokemonRepository->missingPokemons($userId, $type);

        $numbers = [];

        array_map(function ($result) use (&$numbers) {
            $numbers[$result['number']] = $result['number'];
        }, $results);

        foreach ($results as $result) {
            if (isset($evolutionChains[$result['evolution_chain_id']])) {
                foreach ($evolutionChains[$result['evolution_chain_id']] as $evolution) {
                    $numbers[] = $evolution;
                    if ($result['number'] === $evolution) {
                        break;
                    }
                }
            }
        }

        sort($numbers);
        $numbers = array_unique($numbers);

        return implode(',', $numbers);
    }
}