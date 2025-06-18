<?php

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DebugController extends AbstractController
{
    #[Route('/debug', name: 'app_debug')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        return $this->render('debug/index.html.twig', [
            'allPokemon' => $pokemonRepository->findAll(),
        ]);
    }
}
