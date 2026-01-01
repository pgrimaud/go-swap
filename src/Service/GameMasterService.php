<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-import-type Move from \App\PHPStan\Types\MoveTypes
 * @phpstan-import-type Pokemon from \App\PHPStan\Types\PokemonTypes
 */
readonly class GameMasterService
{
    public function __construct(
        private string $pvpokeBaseUrl,
        private HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @return array<int, Pokemon>
     */
    public function getPokemons(): array
    {
        $response = $this->httpClient->request('GET', $this->pvpokeBaseUrl . '/src/data/gamemaster/pokemon.json');
        /** @var array<int, Pokemon> $data */
        $data = json_decode($response->getContent(), true);

        return $data;
    }

    /**
     * @return array<int, Move>
     */
    public function getMoves(): array
    {
        $response = $this->httpClient->request('GET', $this->pvpokeBaseUrl . '/src/data/gamemaster/moves.json');
        /** @var array<int, Move> $data */
        $data = json_decode($response->getContent(), true);

        return $data;
    }

    public function getPvPokeVersion(): ?string
    {
        $response = $this->httpClient->request('GET', $this->pvpokeBaseUrl . '/src/header.php');
        $content = $response->getContent();
        $lines = explode("\n", $content);

        if (!isset($lines[1])) {
            return null;
        }

        // Extract version from line 2 (format: $SITE_VERSION = 'X.X.X';)
        if (!preg_match('/\$SITE_VERSION\s*=\s*["\']([^"\']+)["\']/', $lines[1], $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @return array<int, array{speciesId: string, speciesName: string, rating: int, score: float, moveset: array<int, string>}>
     */
    public function getRankings(string $league, string $version): array
    {
        $cpLimits = [
            'great' => 1500,
            'ultra' => 2500,
            'master' => 10000,
        ];

        $cpLimit = $cpLimits[$league] ?? 1500;
        $url = sprintf('https://pvpoke.com/data/rankings/all/overall/rankings-%d.json?v=%s', $cpLimit, $version);

        $response = $this->httpClient->request('GET', $url);
        /** @var array<int, array{speciesId: string, speciesName: string, rating: int, score: float, moveset: array<int, string>}> $data */
        $data = json_decode($response->getContent(), true);

        return $data;
    }
}
