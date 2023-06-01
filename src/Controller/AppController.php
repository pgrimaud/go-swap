<?php

namespace App\Controller;

use App\Helper\PokedexHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(
        PokemonRepository $pokemonRepository,
        UserPokemonRepository $userPokemonRepository
    ): Response {

        $totalPokemon = $pokemonRepository->count([]);

        $pokedexs = [];

        foreach (PokedexHelper::POKEDEX as $type => $name) {
            $pokedexs[] = [
                'type' => $type,
                'name' => $name,
                'caught' => $userPokemonRepository->countByPokedex($this->getUser(), $type),
                'total' => $pokemonRepository->countUnique($type === 'shiny'),
            ];
        }

        return $this->render('app/index.html.twig', [
            'pokedexs' => $pokedexs
        ]);
    }


    public function users(UserRepository $userRepository): Response
    {
        return $this->render('app/users.html.twig', [
            'users' => $userRepository->findAll()
        ]);
    }

    #[Route('/trade', name: 'app_trade')]
    public function trade(UserRepository $userRepository): Response
    {
        ;
        return $this->render('app/trade.html.twig',[
            'users' => $userRepository->findAll()
        ]);
    }
}
