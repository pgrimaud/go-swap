<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\CustomListPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/custom-lists')]
#[IsGranted('ROLE_USER')]
final class CustomListApiController extends AbstractController
{
    public function __construct(
        private readonly CustomListPokemonRepository $customListPokemonRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/pokemon/{id}', name: 'api_custom_list_pokemon_delete', methods: ['DELETE'])]
    public function deletePokemon(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $customListPokemon = $this->customListPokemonRepository->find($id);

        if (!$customListPokemon) {
            return $this->json(['error' => 'Pokemon not found in list'], Response::HTTP_NOT_FOUND);
        }

        $customList = $customListPokemon->getCustomList();

        if (!$customList) {
            return $this->json(['error' => 'List not found'], Response::HTTP_NOT_FOUND);
        }

        // Check that the user owns the custom list
        if ($customList->getUser() !== $user) {
            return $this->json(['error' => 'You cannot remove this pokemon'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($customListPokemon);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Pokemon removed from list',
        ]);
    }

    #[Route('/{listId}/pokemon/{pokemonId}', name: 'api_custom_list_pokemon_add', methods: ['POST'])]
    public function addPokemon(int $listId, int $pokemonId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $customList = $this->entityManager->getRepository(\App\Entity\CustomList::class)->find($listId);

        if (!$customList) {
            return $this->json(['error' => 'List not found'], Response::HTTP_NOT_FOUND);
        }

        // Check that the user owns the custom list
        if ($customList->getUser() !== $user) {
            return $this->json(['error' => 'You cannot add to this list'], Response::HTTP_FORBIDDEN);
        }

        $pokemon = $this->entityManager->getRepository(\App\Entity\Pokemon::class)->find($pokemonId);

        if (!$pokemon) {
            return $this->json(['error' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
        }

        // Get isShiny from request body
        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getContent(), true);
        $isShiny = isset($data['isShiny']) && $data['isShiny'] === true;

        // Check if Pokemon already in list (with same variant)
        $existing = $this->customListPokemonRepository->findOneBy([
            'customList' => $customList,
            'pokemon' => $pokemon,
            'isShiny' => $isShiny,
        ]);

        if ($existing) {
            return $this->json(['error' => 'Pokemon already in list'], Response::HTTP_CONFLICT);
        }

        // Create new entry
        $customListPokemon = new \App\Entity\CustomListPokemon();
        $customListPokemon->setCustomList($customList);
        $customListPokemon->setPokemon($pokemon);
        $customListPokemon->setIsShiny($isShiny);
        $customListPokemon->setPosition($customList->getCustomListPokemon()->count());

        $this->entityManager->persist($customListPokemon);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Pokemon added to list',
            'data' => [
                'id' => $customListPokemon->getId(),
                'isShiny' => $customListPokemon->isShiny(),
                'pokemon' => [
                    'id' => $pokemon->getId(),
                    'name' => $pokemon->getName(),
                    'number' => $pokemon->getNumber(),
                    'slug' => $pokemon->getSlug(),
                ],
            ],
        ], Response::HTTP_CREATED);
    }
}
