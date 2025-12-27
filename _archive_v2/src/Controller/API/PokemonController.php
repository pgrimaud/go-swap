<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\User;
use App\Entity\UserPvPPokemon;
use App\Enum\League;
use App\Enum\Type;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokemonController extends AbstractController
{
    #[Route('/api/pokemon/moves/{id}', name: 'app_api_pokemon_moves', methods: ['GET'])]
    public function moves(Pokemon $pokemon): Response
    {
        $fastMoves = $pokemon->getPokemonMoves()->filter(
            fn (PokemonMove $move) => Move::FAST_MOVE === $move->getMove()?->getClass()
        );

        $chargedMoves = $pokemon->getPokemonMoves()->filter(
            fn (PokemonMove $move) => Move::CHARGED_MOVE === $move->getMove()?->getClass()
        );

        // Sort fastMoves by name
        $fastMoves = $fastMoves->toArray();
        usort($fastMoves, function (PokemonMove $a, PokemonMove $b) {
            return strcmp($a->getMove()?->getName() ?? '', $b->getMove()?->getName() ?? '');
        });

        // Sort chargedMoves by name
        $chargedMoves = $chargedMoves->toArray();
        usort($chargedMoves, function (PokemonMove $a, PokemonMove $b) {
            return strcmp($a->getMove()?->getName() ?? '', $b->getMove()?->getName() ?? '');
        });

        return $this->json([
            'fastMoves' => array_map(
                fn (PokemonMove $move) => [
                    'id' => $move->getMove()?->getId(),
                    'name' => $move->getMove()?->getName(),
                    'isElite' => $move->isElite(),
                ],
                $fastMoves
            ),
            'chargedMoves' => array_map(
                fn (PokemonMove $move) => [
                    'id' => $move->getMove()?->getId(),
                    'name' => $move->getMove()?->getName(),
                    'isElite' => $move->isElite(),
                ],
                $chargedMoves
            ),
        ]);
    }

    #[Route('/api/pokemon', name: 'app_api_pokemon_add', methods: ['POST'])]
    public function add(
        Request $request,
        PokemonRepository $pokemonRepository,
        MoveRepository $moveRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $pokemon = $pokemonRepository->find($request->get('pokemonId'));

        if (!$pokemon instanceof Pokemon) {
            return $this->json(['error' => 'Pokemon not found'], Response::HTTP_NOT_FOUND);
        }

        $fastMove = $moveRepository->find($request->get('fastMove'));
        $chargedMove1 = $moveRepository->find($request->get('chargedMove1'));
        $chargedMove2 = $moveRepository->find($request->get('chargedMove2'));

        if (!$fastMove instanceof Move || !$chargedMove1 instanceof Move) {
            return $this->json(['error' => 'Move not found'], Response::HTTP_NOT_FOUND);
        }

        $pvpPokemon = new UserPvPPokemon();
        $pvpPokemon->setPokemon($pokemon);

        $pvpPokemon->setStamina((int) $request->request->get('stamina', 0));
        $pvpPokemon->setDefense((int) $request->request->get('defense', 0));
        $pvpPokemon->setAttack((int) $request->request->get('attack', 0));
        $pvpPokemon->setLeagueRank((int) $request->request->get('leagueRank', 0));

        $pvpPokemon->setLeague((string) $request->request->get('league', League::GREAT_LEAGUE));

        $pvpPokemon->setFastMove($fastMove);
        $pvpPokemon->setChargedMove1($chargedMove1);
        $pvpPokemon->setChargedMove2($chargedMove2);

        $pvpPokemon->setType((string) $request->request->get('type', Type::TYPE_NORMAL));

        /** @var User $user */
        $user = $this->getUser();
        $pvpPokemon->setUser($user);

        $entityManager->persist($pvpPokemon);
        $entityManager->flush();

        return $this->json([], Response::HTTP_CREATED);
    }
}
