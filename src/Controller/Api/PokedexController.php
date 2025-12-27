<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\PokemonRepository;
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
    ): JsonResponse {
        $variant = $request->query->get('variant', '');
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 50);

        // Build query
        $queryBuilder = $pokemonRepository->createQueryBuilder('p')
            ->leftJoin('p.types', 't')
            ->addSelect('t')
            ->orderBy('p.number', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        // Apply search filter
        if ($search !== '') {
            $queryBuilder
                ->andWhere('p.name LIKE :search OR p.number = :number')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('number', (int) $search);
        }

        // TODO: Apply variant filter when UserPokemon entity is ready

        $pokemon = $queryBuilder->getQuery()->getResult();
        $total = $pokemonRepository->count([]);

        return $this->json([
            'pokemon' => $pokemon,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'hasMore' => ($page * $perPage) < $total,
            'variant' => $variant,
            'search' => $search,
        ], 200, [], ['groups' => ['pokemon:read']]);
    }
}
