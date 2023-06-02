<?php

namespace App\Controller;

use App\Helper\PokedexHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/trade/{id}', name: 'app_trade')]
    public function trade(UserRepository $userRepository, UserPokemonRepository $userPokemonRepository, $id,PokemonRepository $pokemonRepository): Response
    {
        return $this->render('app/trade.html.twig',[
            'friend' => $userRepository->findOneBy(['id' => $id]),
            'userPokemons'=> $pokemonRepository->missingShinyPokemons($this->getUser()->getId(), $userRepository->findOneBy(['id' => $id])->getId()),
            'friendPokemons'=> $pokemonRepository->missingShinyPokemons($userRepository->findOneBy(['id' => $id])->getId(), $this->getUser()->getId())
        ]);
    }
}
