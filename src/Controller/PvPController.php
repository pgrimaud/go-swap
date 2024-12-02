<?php

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PvPController extends AbstractController
{
    #[Route('/pvp', name: 'app_pvp_index')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        return $this->render('pvp/index.html.twig', [
            'pokemons' =>  $pokemonRepository->findAll(),
            'evolutionChains' => $pokemonRepository->getEvolutionsChains(),
            'userPokemons' => $pokemonRepository->getUserPvPPokemon($this->getUser()),
        ]);
    }
}
