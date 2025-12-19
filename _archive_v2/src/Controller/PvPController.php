<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPvPPokemon;
use App\Enum\League;
use App\Enum\Type;
use App\Repository\PokemonRepository;
use App\Repository\TypeEffectivenessRepository;
use App\Repository\TypeRepository;
use App\Repository\UserPvPPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PvPController extends AbstractController
{
    #[Route('/pvp', name: 'app_pvp_index')]
    public function index(UserPvPPokemonRepository $userPvPPokemonRepository): Response
    {
        return $this->render('pvp/index.html.twig', [
            'totalUserPokemon' => count($userPvPPokemonRepository->findBy(['user' => $this->getUser()])),
        ]);
    }

    #[Route('/pvp/pokemon', name: 'app_pvp_pokemon')]
    public function pokemon(
        PokemonRepository $pokemonRepository,
        UserPvPPokemonRepository $userPvPPokemonRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $userPokemon = $userPvPPokemonRepository->findForUserOrderedByNumber($user);

        $pokemon = $pokemonRepository->findAll();

        return $this->render('pvp/pokemon_cards.html.twig', [
            'allPokemon' => $pokemon,
            'userPokemon' => $userPokemon,
            'leagues' => [
                League::LITTLE_CUP => 'Little Cup',
                League::GREAT_LEAGUE => 'Great League',
                League::ULTRA_LEAGUE => 'Ultra League',
            ],
            'types' => [
                Type::TYPE_NORMAL => 'Normal',
                Type::TYPE_SHADOW => 'Shadow',
                Type::TYPE_PURIFIED => 'Purified',
            ],
            'totalUserPokemon' => count($userPokemon),
            'totalUserPokemonByLeague' => [
                League::LITTLE_CUP => count($userPvPPokemonRepository->findBy(['user' => $user, 'league' => League::LITTLE_CUP])),
                League::GREAT_LEAGUE => count($userPvPPokemonRepository->findBy(['user' => $user, 'league' => League::GREAT_LEAGUE])),
                League::ULTRA_LEAGUE => count($userPvPPokemonRepository->findBy(['user' => $user, 'league' => League::ULTRA_LEAGUE])),
            ],
        ]);
    }

    #[Route('/pvp/pokemon/delete/{id}', name: 'app_pvp_pokemon_delete')]
    public function deletePokemon(UserPvPPokemon $pokemon, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($pokemon);
        $entityManager->flush();

        return $this->redirectToRoute('app_pvp_pokemon');
    }

    #[Route('/pvp/types', name: 'app_pvp_types')]
    public function types(
        TypeRepository $typeRepository,
        TypeEffectivenessRepository $typeEffectivenessRepository,
    ): Response {
        $types = $typeRepository->findAll();
        $effectiveness = $typeEffectivenessRepository->findAll();

        $typeData = [];
        foreach ($types as $type) {
            $strongAgainst = [];
            $weakAgainst = [];
            $resistantTo = [];
            $vulnerableTo = [];
            foreach ($effectiveness as $eff) {
                if ($eff->getSourceType() === $type) {
                    if ($eff->getMultiplier() > 1) {
                        $strongAgainst[] = $eff->getTargetType();
                    } elseif ($eff->getMultiplier() < 1) {
                        $weakAgainst[] = $eff->getTargetType();
                    }
                }
                if ($eff->getTargetType() === $type) {
                    if ($eff->getMultiplier() < 1) {
                        $resistantTo[] = $eff->getSourceType();
                    } elseif ($eff->getMultiplier() > 1) {
                        $vulnerableTo[] = $eff->getSourceType();
                    }
                }
            }
            $typeData[] = [
                'type' => $type,
                'strongAgainst' => $strongAgainst,
                'weakAgainst' => $weakAgainst,
                'resistantTo' => $resistantTo,
                'vulnerableTo' => $vulnerableTo,
            ];
        }

        return $this->render('pvp/types.html.twig', [
            'typeData' => $typeData,
        ]);
    }

    #[Route('/pvp/pokemon/details', name: 'app_pvp_pokemon_details')]
    public function pokemonDetails(UserPvPPokemonRepository $userPvPPokemonRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userPokemonRank1 = $userPvPPokemonRepository->findForUserOrderedByNumber($user, 1, 1);
        $userPokemonTop10 = $userPvPPokemonRepository->findForUserOrderedByNumber($user, 1, 10);
        $userPokemonTop30 = $userPvPPokemonRepository->findForUserOrderedByNumber($user, 1, 30);
        $userPokemonTop100 = $userPvPPokemonRepository->findForUserOrderedByNumber($user, 1, 100);
        $userPokemon = $userPvPPokemonRepository->findForUserOrderedByNumber($user);

        return $this->render('pvp/pokemon/details.html.twig', [
            'totalUserPokemon' => count($userPokemon),
            'userPokemonRank1' => $userPokemonRank1,
            'userPokemonTop10' => $userPokemonTop10,
            'userPokemonTop30' => $userPokemonTop30,
            'userPokemonTop100' => $userPokemonTop100,
            'userPokemon' => $userPokemon,
        ]);
    }
}
