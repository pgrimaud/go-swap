<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Move;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MoveFixtures extends Fixture implements DependentFixtureInterface
{
    public const string MOVE_TACKLE = 'move_tackle';
    public const string MOVE_EMBER = 'move_ember';
    public const string MOVE_WATER_GUN = 'move_water_gun';
    public const string MOVE_THUNDER_SHOCK = 'move_thunder_shock';
    public const string MOVE_VINE_WHIP = 'move_vine_whip';
    public const string MOVE_CONFUSION = 'move_confusion';
    public const string MOVE_DRAGON_BREATH = 'move_dragon_breath';
    public const string MOVE_SNARL = 'move_snarl';
    public const string MOVE_BODY_SLAM = 'move_body_slam';
    public const string MOVE_FLAMETHROWER = 'move_flamethrower';
    public const string MOVE_HYDRO_PUMP = 'move_hydro_pump';
    public const string MOVE_THUNDERBOLT = 'move_thunderbolt';
    public const string MOVE_SOLAR_BEAM = 'move_solar_beam';
    public const string MOVE_PSYCHIC = 'move_psychic';
    public const string MOVE_DRAGON_CLAW = 'move_dragon_claw';
    public const string MOVE_FOUL_PLAY = 'move_foul_play';

    public function load(ObjectManager $manager): void
    {
        // Fast Moves
        $fastMoves = [
            [
                'name' => 'Tackle',
                'slug' => 'tackle',
                'type_ref' => TypeFixtures::TYPE_NORMAL,
                'power' => 3,
                'energy' => 0,
                'energy_gain' => 3,
                'cooldown' => 1,
                'category' => 'fast',
                'class' => 'physical',
                'ref' => self::MOVE_TACKLE,
            ],
            [
                'name' => 'Ember',
                'slug' => 'ember',
                'type_ref' => TypeFixtures::TYPE_FIRE,
                'power' => 6,
                'energy' => 0,
                'energy_gain' => 6,
                'cooldown' => 2,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_EMBER,
            ],
            [
                'name' => 'Water Gun',
                'slug' => 'water-gun',
                'type_ref' => TypeFixtures::TYPE_WATER,
                'power' => 5,
                'energy' => 0,
                'energy_gain' => 5,
                'cooldown' => 1,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_WATER_GUN,
            ],
            [
                'name' => 'Thunder Shock',
                'slug' => 'thunder-shock',
                'type_ref' => TypeFixtures::TYPE_ELECTRIC,
                'power' => 3,
                'energy' => 0,
                'energy_gain' => 9,
                'cooldown' => 2,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_THUNDER_SHOCK,
            ],
            [
                'name' => 'Vine Whip',
                'slug' => 'vine-whip',
                'type_ref' => TypeFixtures::TYPE_GRASS,
                'power' => 5,
                'energy' => 0,
                'energy_gain' => 8,
                'cooldown' => 2,
                'category' => 'fast',
                'class' => 'physical',
                'ref' => self::MOVE_VINE_WHIP,
            ],
            [
                'name' => 'Confusion',
                'slug' => 'confusion',
                'type_ref' => TypeFixtures::TYPE_PSYCHIC,
                'power' => 16,
                'energy' => 0,
                'energy_gain' => 12,
                'cooldown' => 4,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_CONFUSION,
            ],
            [
                'name' => 'Dragon Breath',
                'slug' => 'dragon-breath',
                'type_ref' => TypeFixtures::TYPE_DRAGON,
                'power' => 4,
                'energy' => 0,
                'energy_gain' => 3,
                'cooldown' => 1,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_DRAGON_BREATH,
            ],
            [
                'name' => 'Snarl',
                'slug' => 'snarl',
                'type_ref' => TypeFixtures::TYPE_DARK,
                'power' => 6,
                'energy' => 0,
                'energy_gain' => 12,
                'cooldown' => 3,
                'category' => 'fast',
                'class' => 'special',
                'ref' => self::MOVE_SNARL,
            ],
        ];

        foreach ($fastMoves as $moveData) {
            $move = new Move();
            $move->setName($moveData['name']);
            $move->setSlug($moveData['slug']);
            $move->setType($this->getReference($moveData['type_ref'], Type::class));
            $move->setPower($moveData['power']);
            $move->setEnergy($moveData['energy']);
            $move->setEnergyGain($moveData['energy_gain']);
            $move->setCooldown($moveData['cooldown']);
            $move->setCategory($moveData['category']);
            $move->setClass($moveData['class']);
            $move->setHash(md5($moveData['slug']));

            $manager->persist($move);
            $this->addReference($moveData['ref'], $move);
        }

        /** @var array<int, array{
         * name: string,
         *     slug: string,
         *     type_ref: string,
         *     power: int,
         *     energy: int,
         *     energy_gain: int,
         *     cooldown: int,
         *     category: string,
         *     class: string,
         *     buff_attack?: int,
         *     buff_defense?: int,
         *     buff_target?: string,
         *     buff_chance?: float,
         *     ref: string
         * }> $chargedMoves  */
        $chargedMoves = [
            [
                'name' => 'Body Slam',
                'slug' => 'body-slam',
                'type_ref' => TypeFixtures::TYPE_NORMAL,
                'power' => 60,
                'energy' => 35,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'physical',
                'ref' => self::MOVE_BODY_SLAM,
            ],
            [
                'name' => 'Flamethrower',
                'slug' => 'flamethrower',
                'type_ref' => TypeFixtures::TYPE_FIRE,
                'power' => 70,
                'energy' => 55,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'special',
                'ref' => self::MOVE_FLAMETHROWER,
            ],
            [
                'name' => 'Hydro Pump',
                'slug' => 'hydro-pump',
                'type_ref' => TypeFixtures::TYPE_WATER,
                'power' => 130,
                'energy' => 75,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'special',
                'ref' => self::MOVE_HYDRO_PUMP,
            ],
            [
                'name' => 'Thunderbolt',
                'slug' => 'thunderbolt',
                'type_ref' => TypeFixtures::TYPE_ELECTRIC,
                'power' => 90,
                'energy' => 55,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'special',
                'ref' => self::MOVE_THUNDERBOLT,
            ],
            [
                'name' => 'Solar Beam',
                'slug' => 'solar-beam',
                'type_ref' => TypeFixtures::TYPE_GRASS,
                'power' => 150,
                'energy' => 80,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'special',
                'ref' => self::MOVE_SOLAR_BEAM,
            ],
            [
                'name' => 'Psychic',
                'slug' => 'psychic',
                'type_ref' => TypeFixtures::TYPE_PSYCHIC,
                'power' => 90,
                'energy' => 55,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'special',
                'buff_attack' => -1,
                'buff_target' => Move::BUFF_TARGET_OPPONENT,
                'buff_chance' => 0.1,
                'ref' => self::MOVE_PSYCHIC,
            ],
            [
                'name' => 'Dragon Claw',
                'slug' => 'dragon-claw',
                'type_ref' => TypeFixtures::TYPE_DRAGON,
                'power' => 50,
                'energy' => 35,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'physical',
                'ref' => self::MOVE_DRAGON_CLAW,
            ],
            [
                'name' => 'Foul Play',
                'slug' => 'foul-play',
                'type_ref' => TypeFixtures::TYPE_DARK,
                'power' => 70,
                'energy' => 45,
                'energy_gain' => 0,
                'cooldown' => 0,
                'category' => 'charged',
                'class' => 'physical',
                'ref' => self::MOVE_FOUL_PLAY,
            ],
        ];

        foreach ($chargedMoves as $moveData) {
            $move = new Move();
            $move->setName($moveData['name']);
            $move->setSlug($moveData['slug']);
            $move->setType($this->getReference($moveData['type_ref'], Type::class));
            $move->setPower($moveData['power']);
            $move->setEnergy($moveData['energy']);
            $move->setEnergyGain($moveData['energy_gain']);
            $move->setCooldown($moveData['cooldown']);
            $move->setCategory($moveData['category']);
            $move->setClass($moveData['class']);
            $move->setHash(md5($moveData['slug']));

            if (isset($moveData['buff_attack'])) {
                $move->setBuffAttack((int) $moveData['buff_attack']);
            }
            if (isset($moveData['buff_defense'])) {
                $move->setBuffDefense((int) $moveData['buff_defense']);
            }
            if (isset($moveData['buff_target'])) {
                $move->setBuffTarget((string) $moveData['buff_target']);
            }
            if (isset($moveData['buff_chance'])) {
                $move->setBuffChance((float) $moveData['buff_chance']);
            }

            $manager->persist($move);
            $this->addReference($moveData['ref'], $move);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TypeFixtures::class,
        ];
    }
}
