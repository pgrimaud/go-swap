<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Pokemon;
use App\Entity\UserPokemon;
use App\Repository\UserPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/user-pokemon')]
#[IsGranted('ROLE_USER')]
final class UserPokemonController extends AbstractController
{
    #[Route('/{id}', name: 'api_user_pokemon_toggle', methods: ['POST'])]
    public function toggleVariant(
        Pokemon $pokemon,
        Request $request,
        UserPokemonRepository $userPokemonRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $content = $request->getContent();
        $data = json_decode($content !== '' ? $content : '{}', true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $variant = $data['variant'] ?? '';
        $value = $data['value'] ?? false;

        if (!in_array($variant, ['normal', 'shiny', 'shadow', 'purified', 'lucky', 'xxl', 'xxs', 'perfect'], true)) {
            return $this->json(['error' => 'Invalid variant'], 400);
        }

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $userPokemon = $userPokemonRepository->findByUserAndPokemon($user, $pokemon);

        if (!$userPokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($user);
            $userPokemon->setPokemon($pokemon);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable());
        }

        $setter = 'setHas' . ucfirst($variant);
        $userPokemon->$setter((bool) $value);

        $hasAnyVariant = $userPokemon->hasNormal() || $userPokemon->hasShiny() || $userPokemon->hasShadow()
            || $userPokemon->hasPurified() || $userPokemon->hasLucky() || $userPokemon->hasXxl()
            || $userPokemon->hasXxs() || $userPokemon->hasPerfect();

        if (!$hasAnyVariant && $userPokemon->getId()) {
            $entityManager->remove($userPokemon);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'deleted' => true,
                'message' => 'Pokemon removed from collection',
            ]);
        }

        $entityManager->persist($userPokemon);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $userPokemon->getId(),
                'pokemonId' => $pokemon->getId(),
                'hasNormal' => $userPokemon->hasNormal(),
                'hasShiny' => $userPokemon->hasShiny(),
                'hasShadow' => $userPokemon->hasShadow(),
                'hasPurified' => $userPokemon->hasPurified(),
                'hasLucky' => $userPokemon->hasLucky(),
                'hasXxl' => $userPokemon->hasXxl(),
                'hasXxs' => $userPokemon->hasXxs(),
                'hasPerfect' => $userPokemon->hasPerfect(),
            ],
        ]);
    }
}
