<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPvPPokemon;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TypeEffectivenessRepository;
use App\Repository\TypeRepository;
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
    public function index(): Response
    {
        return $this->render('pvp/index.html.twig');
    }

    #[Route('/pvp/list', name: 'app_pvp_list')]
    public function list(
        PokemonRepository $pokemonRepository,
        UserPvPPokemonRepository $userPvPPokemonRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $allPokemons = $pokemonRepository->findBy(criteria: [], orderBy: ['number' => 'ASC']);
        $userPokemons = $userPvPPokemonRepository->findBy(['user' => $user]);

        return $this->render('pvp/list.html.twig', [
            'pokemons' => $allPokemons,
            'userPokemons' => $userPokemons,
        ]);
    }

    #[Route('/pvp/types', name: 'app_pvp_types')]
    public function types(
        TypeRepository $typeRepository,
        TypeEffectivenessRepository $effectivenessRepository
    ): Response
    {
        $allTypesWithData = [];

        foreach ($typeRepository->findAll() as $type) {
            $allTypesWithData[$type->getId()] = [
                'type' => $type,
                'strongAgainst' => $effectivenessRepository->getStrongAgainst($type),
                'vulnerableTo' => $effectivenessRepository->getVulnerableTo($type),
                'notEffectiveAgainst' => $effectivenessRepository->getNotEffectiveAgainst($type),
            ];
        }

        return $this->render('pvp/types.html.twig', [
            'allTypesWithData' => $allTypesWithData,
        ]);
    }

    #[Route('/pvp/add', name: 'app_pvp_add')]
    public function add(
        Request $request,
        MoveRepository $moveRepository,
        EntityManagerInterface $entityManager,
        PokemonRepository $pokemonRepository,
    ): Response
    {
        $fastMove = $moveRepository->find($request->request->get('fastMove'));
        $chargedMove1 = $moveRepository->find($request->request->get('chargedMove1'));

        if ($request->request->get('chargedMove2')) {
            $chargedMove2 = $moveRepository->find($request->request->get('chargedMove2'));
        } else {
            $chargedMove2 = null;
        }

        $pokemon = $pokemonRepository->find($request->request->get('pokemonId'));

        /** @var User $user */
        $user = $this->getUser();

        $userPvPPokemon = new UserPvPPokemon();
        $userPvPPokemon->setUser($user);
        $userPvPPokemon->setPokemon($pokemon);
        $userPvPPokemon->setFastMove($fastMove);
        $userPvPPokemon->setChargedMove1($chargedMove1);
        $userPvPPokemon->setChargedMove2($chargedMove2);
        $userPvPPokemon->setLeague((string) $request->request->get('league'));
        $userPvPPokemon->setShadow((bool)$request->request->get('shadow'));
        $userPvPPokemon->setRank((int)$request->request->get('rank'));

        $entityManager->persist($userPvPPokemon);
        $entityManager->flush();

        return $this->json([
            'status' => 'ok',
            'message' => 'Pokemon added',
        ]);
    }

    #[Route('/pvp/display', name: 'app_pvp_display')]
    public function displayOrHide(
        Request $request,
        EntityManagerInterface $entityManager,
        PokemonRepository $pokemonRepository,
        UserPvPPokemonRepository $userPvPPokemonRepository,
    ): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not found');
        }

        $data = $request->request->all();

        $id = $data['id'];
        $display = (bool)$data['hidden'];

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

        //$userPokemon->setHidden($display);

        $entityManager->persist($userPokemon);

        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon updated',
        ]);
    }
}
