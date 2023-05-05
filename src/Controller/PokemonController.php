<?php

namespace App\Controller;

use App\Entity\UserPokemon;
use App\Helper\PokedexHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{
    #[Route('/pokedex', name: 'show_pokedex')]
    public function show(PokemonRepository $pokemonRepository, UserPokemonRepository $userPokemonRepository, EntityManagerInterface $entityManager): Response
    {
        $query = $pokemonRepository->getUserPokemon($this->getUser());

        return $this->render('app/pokedex.html.twig', [
            'pokemons' => $pokemonRepository->findBy([], ['number' => 'ASC']),
            'userPokemons' => $query,
        ]);
    }

    #[Route('/add', name: 'add_pokemon')]
    public function add(
        Request                $request,
        EntityManagerInterface $entityManager,
        PokemonRepository      $pokemonRepository,
        UserPokemonRepository  $userPokemonRepository,
    ): Response
    {
        $user = $this->getUser();
        $data = $request->request->all();

        $id = $data['id'];
        $pokedex = $data['pokedex'];

        $alreadyExist = $userPokemonRepository->findOneBy(['user' => $user, 'pokemon' => $id]);

        if (!$alreadyExist) {
            $pokemon = $pokemonRepository->findOneBy(['number' => $id]);

            $userPokemon = new UserPokemon();
            $userPokemon->setUser($user);
            $userPokemon->setPokemon($pokemon);
            $userPokemon->setShiny(false);
            $userPokemon->setNormal(false);
            $userPokemon->setLucky(false);
            $userPokemon->setThreeStars(false);

        } else {
            $userPokemon = $alreadyExist;
        }

        if (!PokedexHelper::exist($pokedex)) {
            return $this->json([
                'message' => 'Pokedex not found',
            ]);
        }

        $method = 'set' . ucfirst($pokedex);
        $userPokemon->$method(true);

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon added',
        ]);
    }

    #[Route('/delete', name: 'delete_pokemon')]
    public function delete(
        Request                $request,
        EntityManagerInterface $entityManager,
        UserPokemonRepository  $userPokemonRepository
    ): Response
    {
        $data = $request->request->all();
        $id = $data['id'];
        $pokedex = $data['pokedex'];

        $userPokemon = $userPokemonRepository->findOneBy(['user' => $this->getUser(), 'pokemon' => $id]);

        if (!PokedexHelper::exist($pokedex)) {
            return $this->json([
                'message' => 'Pokedex not found',
            ]);
        }

        if ($userPokemon) {
            $method = 'set' . ucfirst($pokedex);
            $userPokemon->$method(false);

            $entityManager->persist($userPokemon);
            $entityManager->flush();
        }

        return $this->json([
            'message' => 'Pokemon deleted',
        ]);
    }
}