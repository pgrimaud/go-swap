<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'api_pokedex', methods: ['GET'])]
    public function index(
        Request $request,
        PokemonRepository $pokemonRepository,
        UserPokemonRepository $userPokemonRepository,
    ): JsonResponse {
        $variant = $request->query->get('variant', '');
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 50);

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        // Build query
        $queryBuilder = $pokemonRepository->createQueryBuilder('p')
            ->orderBy('p.number', 'ASC');

        // Apply search filter
        if ($search !== '') {
            $queryBuilder
                ->andWhere('p.name LIKE :search OR p.number = :number')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('number', (int) $search);
        }

        // Note: variant filter is handled client-side for UI display
        // All Pokemon are returned with their userPokemon data

        // Get total before pagination
        $total = (int) $queryBuilder
            ->select('COUNT(DISTINCT p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $queryBuilder
            ->select('p')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $result = $queryBuilder->getQuery()->getResult();

        if (!is_array($result)) {
            $result = [];
        }

        // Get user's Pokemon collection
        $userPokemonMap = [];
        $userPokemonList = $userPokemonRepository->findAllByUser($user);
        foreach ($userPokemonList as $userPokemon) {
            $pokemonEntity = $userPokemon->getPokemon();
            if ($pokemonEntity !== null) {
                $pokemonId = $pokemonEntity->getId();
                if ($pokemonId !== null) {
                    $userPokemonMap[$pokemonId] = $userPokemon;
                }
            }
        }

        // Enrich Pokemon with user's collection data
        $enrichedPokemon = [];
        foreach ($result as $p) {
            if (!$p instanceof \App\Entity\Pokemon) {
                continue;
            }

            $pokemonId = $p->getId();
            if ($pokemonId === null) {
                continue;
            }

            $userPokemon = $userPokemonMap[$pokemonId] ?? null;

            $enrichedPokemon[] = [
                'id' => $pokemonId,
                'number' => $p->getNumber(),
                'name' => $p->getName(),
                'picture' => $p->getPicture(),
                'generation' => $p->getGeneration(),
                'userPokemon' => $userPokemon ? [
                    'hasNormal' => $userPokemon->hasNormal(),
                    'hasShiny' => $userPokemon->hasShiny(),
                    'hasShadow' => $userPokemon->hasShadow(),
                    'hasPurified' => $userPokemon->hasPurified(),
                    'hasLucky' => $userPokemon->hasLucky(),
                    'hasXxl' => $userPokemon->hasXxl(),
                    'hasXxs' => $userPokemon->hasXxs(),
                    'hasPerfect' => $userPokemon->hasPerfect(),
                ] : null,
            ];
        }

        return $this->json([
            'pokemon' => $enrichedPokemon,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'hasMore' => ($page * $perPage) < $total,
            'variant' => $variant,
            'search' => $search,
        ]);
    }
}
