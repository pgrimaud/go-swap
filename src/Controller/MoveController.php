<?php

namespace App\Controller;

use App\Entity\PokemonMove;
use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoveController extends AbstractController
{
    #[Route('/moves-from-pokemon', name: 'app_move_from_pokemon')]
    public function index(Request $request, PokemonRepository $pokemonRepository): Response
    {
        $pokemon = $pokemonRepository->find($request->request->get('id'));

        if (!$pokemon){
           throw $this->createNotFoundException('Pokemon not found');
        }

        /** @var PokemonMove[] $allFastMoves */
        $allFastMoves = $pokemon->getFastMoves();
        /** @var PokemonMove[] $allChargedMoves */
        $allChargedMoves = $pokemon->getChargedMoves();

        $fastResponse = [];
        foreach ($allFastMoves as $move) {
            $fastResponse[] = [
                'name' => $move->getMove()?->getName(),
                'elite' => $move->isElite(),
                'id' => $move->getId(),
            ];
        }

        $chargedResponse = [];
        foreach ($allChargedMoves as $move) {
            $chargedResponse[] = [
                'name' => $move->getMove()?->getName(),
                'elite' => $move->isElite(),
                'id' => $move->getId(),
            ];
        }

        return $this->json([
            'fast' => $fastResponse,
            'charged' => $chargedResponse,
        ]);
    }
}
