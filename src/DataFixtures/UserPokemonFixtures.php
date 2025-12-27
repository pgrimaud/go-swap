<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Pokemon;
use App\Entity\User;
use App\Entity\UserPokemon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserPokemonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $userRepository = $manager->getRepository(User::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@go-swap.com']);

        if (!$admin instanceof User) {
            return;
        }

        $pokemonRepository = $manager->getRepository(Pokemon::class);

        // Bulbasaur #1 - All variants
        $bulbasaur = $pokemonRepository->findOneBy(['number' => 1]);
        if ($bulbasaur instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($bulbasaur);
            $userPokemon->setHasNormal(true);
            $userPokemon->setHasShiny(true);
            $userPokemon->setHasShadow(true);
            $userPokemon->setHasPurified(true);
            $userPokemon->setHasLucky(true);
            $userPokemon->setHasXxl(true);
            $userPokemon->setHasXxs(true);
            $userPokemon->setHasPerfect(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-30 days'));
            $manager->persist($userPokemon);
        }

        // Charmander #4 - Only Normal and Shiny
        $charmander = $pokemonRepository->findOneBy(['number' => 4]);
        if ($charmander instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($charmander);
            $userPokemon->setHasNormal(true);
            $userPokemon->setHasShiny(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-15 days'));
            $manager->persist($userPokemon);
        }

        // Squirtle #7 - Only Shiny and Lucky
        $squirtle = $pokemonRepository->findOneBy(['number' => 7]);
        if ($squirtle instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($squirtle);
            $userPokemon->setHasShiny(true);
            $userPokemon->setHasLucky(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-7 days'));
            $manager->persist($userPokemon);
        }

        // Pikachu #25 - Only Normal
        $pikachu = $pokemonRepository->findOneBy(['number' => 25]);
        if ($pikachu instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($pikachu);
            $userPokemon->setHasNormal(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-3 days'));
            $manager->persist($userPokemon);
        }

        // Mewtwo #150 - Shadow and Purified
        $mewtwo = $pokemonRepository->findOneBy(['number' => 150]);
        if ($mewtwo instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($mewtwo);
            $userPokemon->setHasShadow(true);
            $userPokemon->setHasPurified(true);
            $userPokemon->setHasPerfect(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-1 day'));
            $manager->persist($userPokemon);
        }

        // Mew #151 - XXL and XXS
        $mew = $pokemonRepository->findOneBy(['number' => 151]);
        if ($mew instanceof Pokemon) {
            $userPokemon = new UserPokemon();
            $userPokemon->setUser($admin);
            $userPokemon->setPokemon($mew);
            $userPokemon->setHasNormal(true);
            $userPokemon->setHasXxl(true);
            $userPokemon->setHasXxs(true);
            $userPokemon->setFirstCaughtAt(new \DateTimeImmutable('-5 days'));
            $manager->persist($userPokemon);
        }

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
