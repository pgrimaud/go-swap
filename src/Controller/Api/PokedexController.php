<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Helper\GenerationHelper;
use App\Repository\PokemonRepository;
use App\Repository\UserPokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'api_pokedex', methods: ['GET'])]
    public function index(
        PokemonRepository $pokemonRepository,
        UserPokemonRepository $userPokemonRepository,
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        // Get ALL Pokemon ordered by number
        $allPokemon = $pokemonRepository->findBy([], ['number' => 'ASC', 'id' => 'ASC']);

        // Get available generations sorted by GenerationHelper order
        $pokemonGenerations = array_unique(array_map(
            fn ($p) => $p->getGeneration(),
            $allPokemon
        ));

        $generations = array_values(array_filter(
            GenerationHelper::GENERATIONS,
            fn ($gen) => in_array($gen, $pokemonGenerations, true)
        ));

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
        foreach ($allPokemon as $p) {
            $pokemonId = $p->getId();
            if ($pokemonId === null) {
                continue;
            }

            $userPokemon = $userPokemonMap[$pokemonId] ?? null;

            $evolutionChain = $p->getEvolutionChain();
            $enrichedPokemon[] = [
                'id' => $pokemonId,
                'number' => $p->getNumber(),
                'name' => $p->getName(),
                'picture' => $p->getPicture(),
                'generation' => $p->getGeneration(),
                'evolutionChain' => $evolutionChain ? [
                    'id' => $evolutionChain->getId(),
                    'chainId' => $evolutionChain->getChainId(),
                    'basePokemonName' => $evolutionChain->getBasePokemonName(),
                ] : null,
                'availableVariants' => [
                    'shadow' => $p->isShadow(),
                    'shiny' => $p->isShiny(),
                    'lucky' => $p->isLucky(),
                ],
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
            'generations' => $generations,
        ]);
    }
}
