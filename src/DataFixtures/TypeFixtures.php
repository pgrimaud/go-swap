<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TypeFixtures extends Fixture
{
    public const string TYPE_NORMAL = 'type_normal';
    public const string TYPE_FIRE = 'type_fire';
    public const string TYPE_WATER = 'type_water';
    public const string TYPE_ELECTRIC = 'type_electric';
    public const string TYPE_GRASS = 'type_grass';
    public const string TYPE_PSYCHIC = 'type_psychic';
    public const string TYPE_DRAGON = 'type_dragon';
    public const string TYPE_DARK = 'type_dark';

    public function load(ObjectManager $manager): void
    {
        $types = [
            ['name' => 'Normal', 'slug' => 'normal', 'icon' => 'normal.png', 'ref' => self::TYPE_NORMAL],
            ['name' => 'Fire', 'slug' => 'fire', 'icon' => 'fire.png', 'ref' => self::TYPE_FIRE],
            ['name' => 'Water', 'slug' => 'water', 'icon' => 'water.png', 'ref' => self::TYPE_WATER],
            ['name' => 'Electric', 'slug' => 'electric', 'icon' => 'electric.png', 'ref' => self::TYPE_ELECTRIC],
            ['name' => 'Grass', 'slug' => 'grass', 'icon' => 'grass.png', 'ref' => self::TYPE_GRASS],
            ['name' => 'Psychic', 'slug' => 'psychic', 'icon' => 'psychic.png', 'ref' => self::TYPE_PSYCHIC],
            ['name' => 'Dragon', 'slug' => 'dragon', 'icon' => 'dragon.png', 'ref' => self::TYPE_DRAGON],
            ['name' => 'Dark', 'slug' => 'dark', 'icon' => 'dark.png', 'ref' => self::TYPE_DARK],
        ];

        foreach ($types as $typeData) {
            $type = new Type();
            $type->setName($typeData['name']);
            $type->setSlug($typeData['slug']);
            $type->setIcon($typeData['icon']);

            $manager->persist($type);
            $this->addReference($typeData['ref'], $type);
        }

        $manager->flush();
    }
}
