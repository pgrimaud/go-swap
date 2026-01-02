<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use App\Helper\GenerationHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PokemonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $pokemonData = [
            [
                'number' => 1,
                'name' => 'Bulbasaur',
                'slug' => 'bulbasaur',
                'types' => [TypeFixtures::TYPE_GRASS],
                'attack' => 118,
                'defense' => 111,
                'stamina' => 128,
                'picture' => 'bulbasaur.png',
                'shiny_picture' => 'bulbasaur-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_VINE_WHIP, MoveFixtures::MOVE_TACKLE],
                'charged_moves' => [MoveFixtures::MOVE_SOLAR_BEAM, MoveFixtures::MOVE_BODY_SLAM],
            ],
            [
                'number' => 4,
                'name' => 'Charmander',
                'slug' => 'charmander',
                'types' => [TypeFixtures::TYPE_FIRE],
                'attack' => 116,
                'defense' => 93,
                'stamina' => 118,
                'picture' => 'charmander.png',
                'shiny_picture' => 'charmander-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => true,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_EMBER, MoveFixtures::MOVE_TACKLE],
                'charged_moves' => [MoveFixtures::MOVE_FLAMETHROWER],
            ],
            [
                'number' => 7,
                'name' => 'Squirtle',
                'slug' => 'squirtle',
                'types' => [TypeFixtures::TYPE_WATER],
                'attack' => 94,
                'defense' => 121,
                'stamina' => 127,
                'picture' => 'squirtle.png',
                'shiny_picture' => 'squirtle-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => true,
                'fast_moves' => [MoveFixtures::MOVE_WATER_GUN],
                'charged_moves' => [MoveFixtures::MOVE_HYDRO_PUMP, MoveFixtures::MOVE_BODY_SLAM],
            ],
            [
                'number' => 25,
                'name' => 'Pikachu',
                'slug' => 'pikachu',
                'types' => [TypeFixtures::TYPE_ELECTRIC],
                'attack' => 112,
                'defense' => 96,
                'stamina' => 111,
                'picture' => 'pikachu.png',
                'shiny_picture' => 'pikachu-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_THUNDER_SHOCK],
                'charged_moves' => [MoveFixtures::MOVE_THUNDERBOLT, MoveFixtures::MOVE_BODY_SLAM],
            ],
            [
                'number' => 63,
                'name' => 'Abra',
                'slug' => 'abra',
                'types' => [TypeFixtures::TYPE_PSYCHIC],
                'attack' => 195,
                'defense' => 82,
                'stamina' => 93,
                'picture' => 'abra.png',
                'shiny_picture' => 'abra-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_CONFUSION],
                'charged_moves' => [MoveFixtures::MOVE_PSYCHIC],
            ],
            [
                'number' => 147,
                'name' => 'Dratini',
                'slug' => 'dratini',
                'types' => [TypeFixtures::TYPE_DRAGON],
                'attack' => 119,
                'defense' => 91,
                'stamina' => 121,
                'picture' => 'dratini.png',
                'shiny_picture' => 'dratini-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_DRAGON_BREATH],
                'charged_moves' => [MoveFixtures::MOVE_DRAGON_CLAW, MoveFixtures::MOVE_BODY_SLAM],
            ],
            [
                'number' => 228,
                'name' => 'Houndour',
                'slug' => 'houndour',
                'types' => [TypeFixtures::TYPE_DARK, TypeFixtures::TYPE_FIRE],
                'attack' => 152,
                'defense' => 83,
                'stamina' => 128,
                'picture' => 'houndour.png',
                'shiny_picture' => 'houndour-shiny.png',
                'generation' => GenerationHelper::GENERATION_JOHTO,
                'form' => null,
                'shadow' => true,
                'shiny' => true,
                'lucky' => false,
                'fast_moves' => [MoveFixtures::MOVE_SNARL, MoveFixtures::MOVE_EMBER],
                'charged_moves' => [MoveFixtures::MOVE_FOUL_PLAY, MoveFixtures::MOVE_FLAMETHROWER],
            ],
            [
                'number' => 133,
                'name' => 'Eevee',
                'slug' => 'eevee',
                'types' => [TypeFixtures::TYPE_NORMAL],
                'attack' => 104,
                'defense' => 114,
                'stamina' => 146,
                'picture' => 'eevee.png',
                'shiny_picture' => 'eevee-shiny.png',
                'generation' => GenerationHelper::GENERATION_KANTO,
                'form' => null,
                'shadow' => false,
                'shiny' => true,
                'lucky' => true,
                'fast_moves' => [MoveFixtures::MOVE_TACKLE],
                'charged_moves' => [MoveFixtures::MOVE_BODY_SLAM],
            ],
        ];

        foreach ($pokemonData as $data) {
            $pokemon = new Pokemon();
            $pokemon->setNumber($data['number']);
            $pokemon->setName($data['name']);
            $pokemon->setSlug($data['slug']);
            $pokemon->setAttack($data['attack']);
            $pokemon->setDefense($data['defense']);
            $pokemon->setStamina($data['stamina']);
            $pokemon->setPicture($data['picture']);
            $pokemon->setShinyPicture($data['shiny_picture']);
            $pokemon->setGeneration($data['generation']);
            $pokemon->setForm($data['form']);
            $pokemon->setShadow($data['shadow']);
            $pokemon->setShiny($data['shiny']);
            $pokemon->setLucky($data['lucky']);
            $pokemon->setHash(md5($data['slug']));

            // Add types
            foreach ($data['types'] as $typeRef) {
                $pokemon->addType($this->getReference($typeRef, Type::class));
            }

            $manager->persist($pokemon);

            // Add fast moves
            foreach ($data['fast_moves'] as $moveRef) {
                $pokemonMove = new PokemonMove();
                $pokemonMove->setPokemon($pokemon);
                $pokemonMove->setMove($this->getReference($moveRef, Move::class));
                $pokemonMove->setElite(false);

                $manager->persist($pokemonMove);
            }

            // Add charged moves
            foreach ($data['charged_moves'] as $moveRef) {
                $pokemonMove = new PokemonMove();
                $pokemonMove->setPokemon($pokemon);
                $pokemonMove->setMove($this->getReference($moveRef, Move::class));
                $pokemonMove->setElite(false);

                $manager->persist($pokemonMove);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TypeFixtures::class,
            MoveFixtures::class,
        ];
    }
}
