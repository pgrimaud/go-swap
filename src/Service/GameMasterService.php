<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-import-type Move from \App\PhpStan\Types\MoveTypes
 * @phpstan-import-type Pokemon from \App\PhpStan\Types\PokemonTypes
 */
readonly class GameMasterService
{
    public function __construct(
        private string $gameMasterUrl,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array<int, Pokemon>
     */
    public function getPokemons(): array
    {
        $response = $this->httpClient->request('GET', $this->gameMasterUrl . '/pokemon.json');
        /** @var array<int, Pokemon> $data */
        $data = json_decode($response->getContent(), true);

        return $data;
    }

    /**
     * @return array<int, Move>
     */
    public function getMoves(): array
    {
        $response = $this->httpClient->request('GET', $this->gameMasterUrl . '/moves.json');
        /** @var array<int, Move> $data */
        $data = json_decode($response->getContent(), true);

        return $data;
    }
}
