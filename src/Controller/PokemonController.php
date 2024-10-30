<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPokemon;
use App\Helper\GenerationHelper;
use App\Helper\PokedexHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{
    #[Route('/pokedex', name: 'show_pokedex')]
    public function show(PokemonRepository $pokemonRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $userPokemons = $pokemonRepository->getUserPokemon($user);

        $generations = [];

        foreach (GenerationHelper::GENERATIONS as $type => $name) {
            $generations[] = [
                'type' => $type,
                'name' => $name,
            ];
        }

        $missingByDex = [];

        foreach (PokedexHelper::POKEDEX as $type => $name) {
            $missingByDex[$type] = $pokemonRepository->missingPokemons((int) $user->getId(), $type);
        }

        return $this->render('app/pokedex.html.twig', [
            'pokemons' => $pokemonRepository->findBy([], [
                'number' => 'ASC',
                'id' => 'ASC',
            ]),
            'userPokemons' => $userPokemons,
            'generations' => $generations,
            'evolutionChains' => $pokemonRepository->getEvolutionsChains(),
            'missingByDex' => $missingByDex
        ]);
    }

    #[Route('/pokedex-friend/{id}', name: 'showFriends_pokedex')]
    public function showFriends(int $id, UserRepository $userRepository, PokemonRepository $pokemonRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $userPokemons = $pokemonRepository->getUserPokemon($user);

        $generations = [];

        foreach (GenerationHelper::GENERATIONS as $type => $name) {
            $generations[] = [
                'type' => $type,
                'name' => $name,
                'count' => $pokemonRepository->getCountByGeneration($type)
            ];
        }

        return $this->render('app/pokedex.html.twig', [
            'pokemons' => $pokemonRepository->findBy([], ['number' => 'ASC', 'id' => 'ASC']),
            'userPokemons' => $userPokemons,
            'pokedexUsername' => $userRepository->findOneBy(['id' => $id]),
            'generations' => $generations,
            'lastUpdate' => $user->getUpdatedAt(),
            'evolutionChains' => $pokemonRepository->getEvolutionsChains(),
        ]);
    }

    #[Route('/add', name: 'add_pokemon')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        PokemonRepository $pokemonRepository,
        UserPokemonRepository $userPokemonRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $data = $request->request->all();

        $id = $data['id'];
        $pokedex = $data['pokedex'];

        $alreadyExist = $userPokemonRepository->findOneBy(
            ['user' => $user, 'pokemon' => $id]
        );

        if (!$alreadyExist) {
            $pokemon = $pokemonRepository->findOneBy(['id' => $id]);

            $userPokemon = new UserPokemon();
            $userPokemon->setUser($user);
            $userPokemon->setPokemon($pokemon);
            $userPokemon->setShiny(false);
            $userPokemon->setNormal(false);
            $userPokemon->setLucky(false);
            $userPokemon->setThreeStars(false);
            $userPokemon->setShadow(false);
            $userPokemon->setPurified(false);
            $userPokemon->setShinyThreeStars(false);
            $userPokemon->setPerfect(false);
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

        if ($pokedex === 'shiny') {
            $userPokemon->setNumberShiny(1);
        }

        $entityManager->persist($userPokemon);

        $user->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($user);

        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon added',
        ]);
    }

    #[Route('/delete', name: 'delete_pokemon')]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPokemonRepository $userPokemonRepository
    ): Response {
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

            if ($pokedex === 'shiny') {
                $userPokemon->setNumberShiny(0);
            }

            $entityManager->persist($userPokemon);
            $entityManager->flush();
        }

        return $this->json([
            'message' => 'Pokemon deleted',
        ]);
    }

    #[Route('/shiny', name: 'shiny_pokemon')]
    public function addShiny
    (
        Request $request,
        EntityManagerInterface $entityManager,
        UserPokemonRepository $userPokemonRepository,
        PokemonRepository $pokemonRepository,
    ): Response {
        $data = $request->request->all();

        $id = $data['id'];
        $value = $data['value'] ?? 0;

        $userPokemon = $userPokemonRepository->findOneBy(['user' => $this->getUser(), 'pokemon' => $id]);

        if (!$userPokemon) {
            $pokemon = $pokemonRepository->findOneBy(['id' => $id]);

            $userPokemon = new UserPokemon();
            $userPokemon->setUser($this->getUser());
            $userPokemon->setPokemon($pokemon);
            $userPokemon->setNormal(false);
            $userPokemon->setLucky(false);
            $userPokemon->setThreeStars(false);
        }

        $userPokemon->setShiny($value > 0);
        $userPokemon->setNumberShiny($value);

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        return $this->json(['message' => 'Shiny added',]);
    }
}
