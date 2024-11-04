<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\EvolutionChainRepository;
use App\Repository\PokemonRepository;

class MissingPokemon
{
    public function __construct(
        private PokemonRepository $pokemonRepository,
        private EvolutionChainRepository $evolutionChainRepository
    ) {
    }

    public function getMissingByDex(int $userId, string $type): string
    {
        $evolutionChains = $this->evolutionChainRepository->getEvolutionByApiId();
        $results = $this->pokemonRepository->missingPokemons($userId, $type);

        $numbers = [];

        array_map(function ($result) use (&$numbers, $evolutionChains) {
            $numbers[] = $result['number'];
            if (isset($evolutionChains[$result['evolution_chain_id']])) {
                $numbers = array_merge($numbers, $evolutionChains[$result['evolution_chain_id']]);
            }
        }, $results);

        $numbers = array_unique($numbers);
        sort($numbers);

        return implode(',', $numbers);
    }
}