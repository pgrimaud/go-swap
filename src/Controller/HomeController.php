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

        $totalDistinct = $pokemonRepository->countTotalDistinctPokemon();
        $totalShinies = $pokemonRepository->countDistinctShinies();
        $totalShadows = $pokemonRepository->countDistinctShadows();
        $totalLuckies = $pokemonRepository->countDistinctLuckies();

        // Calculate owned counts per variant (distinct pokemon numbers)
        $ownedNormal = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'normal') : 0;
        $ownedShiny = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'shiny') : 0;
        $ownedShadow = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'shadow') : 0;
        $ownedPurified = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'purified') : 0;
        $ownedLucky = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'lucky') : 0;
        $ownedXxl = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'xxl') : 0;
        $ownedXxs = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'xxs') : 0;
        $ownedPerfect = $user ? $userPokemonRepository->countDistinctPokemonByUserAndVariant($user, 'perfect') : 0;

        $generationStats = $pokemonRepository->countPokemonByGeneration();

        $pokedexCategories = [
            [
                'name' => 'Normal',
                'slug' => 'normal',
                'icon' => 'normal.png',
                'count' => $ownedNormal,
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedNormal / $totalDistinct) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'normal') : [],
            ],
            [
                'name' => 'Shiny',
                'slug' => 'shiny',
                'icon' => 'shiny.png',
                'count' => $ownedShiny,
                'total' => $totalShinies,
                'percentage' => $totalShinies > 0 ? round(($ownedShiny / $totalShinies) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'shiny') : [],
            ],
            [
                'name' => 'Shadow',
                'slug' => 'shadow',
                'icon' => 'shadow.png',
                'count' => $ownedShadow,
                'total' => $totalShadows,
                'percentage' => $totalShadows > 0 ? round(($ownedShadow / $totalShadows) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'shadow') : [],
            ],
            [
                'name' => 'Purified',
                'slug' => 'purified',
                'icon' => 'purified.png',
                'count' => $ownedPurified,
                'total' => $totalShadows,
                'percentage' => $totalShadows > 0 ? round(($ownedPurified / $totalShadows) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'purified') : [],
            ],
            [
                'name' => 'Lucky',
                'slug' => 'lucky',
                'icon' => 'lucky.png',
                'count' => $ownedLucky,
                'total' => $totalLuckies,
                'percentage' => $totalLuckies > 0 ? round(($ownedLucky / $totalLuckies) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'lucky') : [],
            ],
            [
                'name' => 'XXL',
                'slug' => 'xxl',
                'icon' => 'xxl.png',
                'count' => $ownedXxl,
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedXxl / $totalDistinct) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'xxl') : [],
            ],
            [
                'name' => 'XXS',
                'slug' => 'xxs',
                'icon' => 'xxs.png',
                'count' => $ownedXxs,
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedXxs / $totalDistinct) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'xxs') : [],
            ],
            [
                'name' => 'Perfect',
                'slug' => 'perfect',
                'icon' => 'perfect.png',
                'count' => $ownedPerfect,
                'total' => $totalDistinct,
                'percentage' => $totalDistinct > 0 ? round(($ownedPerfect / $totalDistinct) * 100) : 0,
                'generationStats' => $user ? $userPokemonRepository->countPokemonByGenerationAndVariant($user, 'perfect') : [],
            ],
        ];

        return $this->render('home/index.html.twig', [
            'pokedexCategories' => $pokedexCategories,
            'generationStats' => $generationStats,
        ]);
    }
}
