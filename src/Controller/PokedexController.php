<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'app_pokedex')]
    public function index(
        Request $request,
        PokemonRepository $pokemonRepository,
    ): Response {
        // Get filter parameters
        $variant = $request->query->get('variant', '');
        $search = $request->query->get('search', '');

        // Get filtered Pokémon
        $queryBuilder = $pokemonRepository->createQueryBuilder('p')
            ->orderBy('p.number', 'ASC');

        // Apply search filter
        if ($search !== '') {
            $queryBuilder
                ->andWhere('p.name LIKE :search OR p.number = :number')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('number', (int) $search);
        }

        // TODO: Apply variant filter when UserPokemon entity is ready
        // This will filter based on owned variants

        $allPokemon = $queryBuilder->getQuery()->getResult();

        return $this->render('pokedex/index.html.twig', [
            'allPokemon' => $allPokemon,
            'currentVariant' => $variant,
            'currentSearch' => $search,
        ]);
    }
}
