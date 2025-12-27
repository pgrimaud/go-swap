<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'app_pokedex')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        return $this->render('pokedex/index.html.twig', [
            'allPokemon' => $pokemonRepository->findAll(),
        ]);
    }
}
