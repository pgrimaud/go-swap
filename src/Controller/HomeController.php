<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PokemonRepository $pokemonRepository,
        UserPokemonRepository $userPokemonRepository,
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        // Get all totals in 4 queries (cacheable)
        $totalDistinct = $pokemonRepository->countTotalDistinctPokemon();
        $totalShinies = $pokemonRepository->countDistinctShinies();
        $totalShadows = $pokemonRepository->countDistinctShadows();
        $totalLuckies = $pokemonRepository->countDistinctLuckies();

        // Get all user owned counts in 1 query (or 0 if not logged in)
        $ownedCounts = $user ? $userPokemonRepository->countAllVariantsByUser($user) : [
            'normal' => 0,
            'shiny' => 0,
            'shadow' => 0,
            'purified' => 0,
            'lucky' => 0,
            'xxl' => 0,
            'xxs' => 0,
            'perfect' => 0,
        ];

        // Get all generation stats in 2 queries (or 0 if not logged in)
        $userGenerationStats = $user ? $userPokemonRepository->countAllVariantsByGenerationForUser($user) : [];
        $totalGenerationStats = $pokemonRepository->countAllVariantsByGeneration();

        $pokedexCategories = [
            [
                'name' => 'Normal',
                'slug' => 'normal',
                'icon' => 'normal.png',
                'count' => $ownedCounts['normal'],
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedCounts['normal'] / $totalDistinct) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['normal'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['normal'] ?? [],
            ],
            [
                'name' => 'Shiny',
                'slug' => 'shiny',
                'icon' => 'shiny.png',
                'count' => $ownedCounts['shiny'],
                'total' => $totalShinies,
                'percentage' => $totalShinies > 0 ? round(($ownedCounts['shiny'] / $totalShinies) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['shiny'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['shiny'] ?? [],
            ],
            [
                'name' => 'Lucky',
                'slug' => 'lucky',
                'icon' => 'lucky.png',
                'count' => $ownedCounts['lucky'],
                'total' => $totalLuckies,
                'percentage' => $totalLuckies > 0 ? round(($ownedCounts['lucky'] / $totalLuckies) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['lucky'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['lucky'] ?? [],
            ],
            [
                'name' => 'XXL',
                'slug' => 'xxl',
                'icon' => 'xxl.png',
                'count' => $ownedCounts['xxl'],
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedCounts['xxl'] / $totalDistinct) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['xxl'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['xxl'] ?? [],
            ],
            [
                'name' => 'XXS',
                'slug' => 'xxs',
                'icon' => 'xxs.png',
                'count' => $ownedCounts['xxs'],
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedCounts['xxs'] / $totalDistinct) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['xxs'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['xxs'] ?? [],
            ],
            [
                'name' => 'Shadow',
                'slug' => 'shadow',
                'icon' => 'shadow.png',
                'count' => $ownedCounts['shadow'],
                'total' => $totalShadows,
                'percentage' => $totalShadows > 0 ? round(($ownedCounts['shadow'] / $totalShadows) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['shadow'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['shadow'] ?? [],
            ],
            [
                'name' => 'Purified',
                'slug' => 'purified',
                'icon' => 'purified.png',
                'count' => $ownedCounts['purified'],
                'total' => $totalShadows,
                'percentage' => $totalShadows > 0 ? round(($ownedCounts['purified'] / $totalShadows) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['purified'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['purified'] ?? [],
            ],
            [
                'name' => 'Perfect',
                'slug' => 'perfect',
                'icon' => 'perfect.png',
                'count' => $ownedCounts['perfect'],
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedCounts['perfect'] / $totalDistinct) * 100, 2) : 0,
                'generationStats' => $userGenerationStats['perfect'] ?? [],
                'totalGenerationStats' => $totalGenerationStats['perfect'] ?? [],
            ],
        ];

        return $this->render('home/index.html.twig', [
            'pokedexCategories' => $pokedexCategories,
        ]);
    }
}
