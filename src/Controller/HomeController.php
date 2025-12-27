<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        $totalDistinct = $pokemonRepository->countTotalDistinctPokemon();
        $totalShinies = $pokemonRepository->countDistinctShinies();
        $totalShadows = $pokemonRepository->countDistinctShadows();
        $totalLuckies = $pokemonRepository->countDistinctLuckies();

        $pokedexCategories = [
            [
                'name' => 'Normal',
                'slug' => '',
                'icon' => 'normal.png',
                'count' => 0,
                'total' => $totalDistinct,
                'percentage' => 0,
            ],
            [
                'name' => 'Shiny',
                'slug' => 'shiny',
                'icon' => 'shiny.png',
                'count' => 0,
                'total' => $totalShinies,
                'percentage' => 0,
            ],
            [
                'name' => 'Shadow',
                'slug' => 'shadow',
                'icon' => 'shadow.png',
                'count' => 0,
                'total' => $totalShadows,
                'percentage' => 0,
            ],
            [
                'name' => 'Purified',
                'slug' => 'purified',
                'icon' => 'purified.png',
                'count' => 0,
                'total' => $totalShadows,
                'percentage' => 0,
            ],
            [
                'name' => 'Lucky',
                'slug' => 'lucky',
                'icon' => 'lucky.png',
                'count' => 0,
                'total' => $totalLuckies,
                'percentage' => 0,
            ],
            [
                'name' => 'XXL',
                'slug' => 'xxl',
                'icon' => 'xxl.png',
                'count' => 0,
                'total' => $totalDistinct,
                'percentage' => 0,
            ],
            [
                'name' => 'XXS',
                'slug' => 'xxs',
                'icon' => 'xxs.png',
                'count' => 0,
                'total' => $totalDistinct,
                'percentage' => 0,
            ],
            [
                'name' => 'Perfect',
                'slug' => 'perfect',
                'icon' => 'perfect.png',
                'count' => 0,
                'total' => $totalDistinct,
                'percentage' => 0,
            ],
        ];

        return $this->render('home/index.html.twig', [
            'pokedexCategories' => $pokedexCategories,
        ]);
    }
}
