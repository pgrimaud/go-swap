<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
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
        TypeRepository $typeRepository,
    ): Response {
        // Get filter parameters
        $generation = $request->query->getInt('generation');
        $typeId = $request->query->getInt('type');
        $search = $request->query->get('search', '');

        // Get all types for filter dropdown
        $types = $typeRepository->findAll();

        // Get filtered Pokémon
        $queryBuilder = $pokemonRepository->createQueryBuilder('p')
            ->leftJoin('p.types', 't')
            ->orderBy('p.number', 'ASC');

        // Apply generation filter
        if ($generation > 0) {
            $ranges = [
                1 => [1, 151],
                2 => [152, 251],
                3 => [252, 386],
                4 => [387, 493],
                5 => [494, 649],
                6 => [650, 721],
                7 => [722, 809],
                8 => [810, 905],
                9 => [906, 1025],
            ];

            if (isset($ranges[$generation])) {
                $queryBuilder
                    ->andWhere('p.number BETWEEN :min AND :max')
                    ->setParameter('min', $ranges[$generation][0])
                    ->setParameter('max', $ranges[$generation][1]);
            }
        }

        // Apply type filter
        if ($typeId > 0) {
            $queryBuilder
                ->andWhere('t.id = :typeId')
                ->setParameter('typeId', $typeId);
        }

        // Apply search filter
        if ($search !== '') {
            $queryBuilder
                ->andWhere('p.name LIKE :search OR p.number = :number')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('number', (int) $search);
        }

        $allPokemon = $queryBuilder->getQuery()->getResult();

        return $this->render('pokedex/index.html.twig', [
            'allPokemon' => $allPokemon,
            'types' => $types,
            'currentGeneration' => $generation,
            'currentType' => $typeId,
            'currentSearch' => $search,
        ]);
    }
}
