<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPvPPokemon;
use App\Repository\PokemonRepository;
use App\Repository\UserPvPPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PvPController extends AbstractController
{
    #[Route('/pvp', name: 'app_pvp_index')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        return $this->render('pvp/index.html.twig', [
            'pokemons' =>  $pokemonRepository->findBy([], [
                'number' => 'ASC',
                'id' => 'ASC',
            ]),
            'evolutionChains' => $pokemonRepository->getEvolutionsChains(),
            'userPokemons' => $pokemonRepository->getUserPvPPokemon($this->getUser()),
        ]);
    }

    #[Route('/pvp/update', name: 'app_pvp_update')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        PokemonRepository $pokemonRepository,
        UserPvPPokemonRepository $userPvPPokemonRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $data = $request->request->all();

        $id = $data['id'];
        $league = $data['league'];

        $rank = $data['rank'] === '' ? 0 : (int) $data['rank'];

        if ($rank < 0 || $rank > 4096) {
            return $this->json([
                'message' => 'Rank must be between 1 and 4096',
            ], 400);
        }

        $alreadyExist = $userPvPPokemonRepository->findOneBy(
            ['user' => $user, 'pokemon' => $id]
        );

        if (!$alreadyExist) {
            $pokemon = $pokemonRepository->findOneBy(['id' => $id]);

            $userPokemon = new UserPvPPokemon();
            $userPokemon->setUser($user);
            $userPokemon->setPokemon($pokemon);
        } else {
            $userPokemon = $alreadyExist;
        }

        $method = 'set' . ucfirst($league) . 'Rank';
        $userPokemon->$method($rank);

        $entityManager->persist($userPokemon);

        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon updated',
        ]);
    }

    #[Route('/pvp/display', name: 'app_pvp_display')]
    public function displayOrHide(
        Request $request,
        EntityManagerInterface $entityManager,
        PokemonRepository $pokemonRepository,
        UserPvPPokemonRepository $userPvPPokemonRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $data = $request->request->all();

        $id = $data['id'];
        $display = (bool) $data['hidden'];

        $alreadyExist = $userPvPPokemonRepository->findOneBy(
            ['user' => $user, 'pokemon' => $id]
        );

        if (!$alreadyExist) {
            $pokemon = $pokemonRepository->findOneBy(['id' => $id]);

            $userPokemon = new UserPvPPokemon();
            $userPokemon->setUser($user);
            $userPokemon->setPokemon($pokemon);
        } else {
            $userPokemon = $alreadyExist;
        }

        $userPokemon->setHidden($display);

        $entityManager->persist($userPokemon);

        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon updated',
        ]);
    }
}
