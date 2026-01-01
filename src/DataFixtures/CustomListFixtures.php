<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CustomList;
use App\Entity\CustomListPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CustomListFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $admin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@go-swap.com']);
        if (!$admin) {
            return;
        }

        // List 1: Trade List with 3 Pokémon
        $tradeList = new CustomList();
        $tradeList->setName('Trade List');
        $tradeList->setDescription('Pokémon available for trading');
        $tradeList->setIsPublic(true);
        $tradeList->setUser($admin);
        $manager->persist($tradeList);

        // Add 3 Pokémon to Trade List (Bulbasaur #1, Charmander #4, Squirtle #7)
        $pokemonNumbers = [1, 4, 7];
        $position = 0;

        foreach ($pokemonNumbers as $number) {
            $pokemon = $manager->getRepository(Pokemon::class)->findOneBy(['number' => $number]);
            if ($pokemon) {
                $listPokemon = new CustomListPokemon();
                $listPokemon->setCustomList($tradeList);
                $listPokemon->setPokemon($pokemon);
                $listPokemon->setPosition($position++);
                $manager->persist($listPokemon);
            }
        }

        // List 2: Favorites (empty)
        $favoritesList = new CustomList();
        $favoritesList->setName('Favorites');
        $favoritesList->setDescription('My favorite Pokémon');
        $favoritesList->setIsPublic(false);
        $favoritesList->setUser($admin);
        $manager->persist($favoritesList);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PokemonFixtures::class,
        ];
    }
}
