<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Entity\UserPokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{

    #[Route('/add', name: 'add_pokemon')]
    public function add(Request $request): Response
    {
        $user = $this->getUser();
        $id = $request->request->get("id");
        $pokedex = $request->request->get("pokedex");

        $pokemon = EntityManagerInterface::class->getRepository(Pokemon::class)->findOneBy(['number' => $id]);

        $userPokemon = new UserPokemon();
        $userPokemon->setUser($user);
        $userPokemon->setPokemon($pokemon);
        switch ($pokedex) {
            case "shiny":
                $userPokemon->setShiny(true);
                break;
            case "normal":
                $userPokemon->setNormal(true);
                break;
            case "lucky":
               $userPokemon->setLucky(true);
                break;
            case "three_stars":
                $userPokemon->setThreeStars(true);
                break;
        }
        EntityManagerInterface::class->flush($userPokemon);
        EntityManagerInterface::class->persist();

        return $this->render('pokemon/index.html.twig', [
            'controller_name' => 'PokemonController',
        ]);
    }

    #[Route('/delete', name: 'delete_pokemon')]
    public function delete(Request $request): Response
    {
        $user = $this->getUser();


        return $this->render('pokemon/index.html.twig', [
            'controller_name' => 'PokemonController',
        ]);
    }



}
