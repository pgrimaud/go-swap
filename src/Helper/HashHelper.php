<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * @phpstan-import-type Move from \App\PHPStan\Types\MoveTypes
 * @phpstan-import-type Pokemon from \App\PHPStan\Types\PokemonTypes
 */
class HashHelper
{
    /**
     * @param Pokemon $pokemon
     */
    public static function fromPokemon(array $pokemon, bool $isShiny, bool $isShadow): string
    {
        return md5(serialize([
            $pokemon['dex'],
            $pokemon['speciesName'],
            $pokemon['speciesId'],
            $pokemon['baseStats']['atk'],
            $pokemon['baseStats']['def'],
            $pokemon['baseStats']['hp'],
            $isShiny,
            $isShadow,
            serialize(array_merge($pokemon['chargedMoves'], $pokemon['fastMoves'], $pokemon['eliteMoves'] ?? [])),
        ]));
    }

    /**
     * @param Move $move
     */
    public static function fromMove(array $move): string
    {
        return md5(serialize([
            $move['moveId'],
            $move['name'],
            $move['type'],
            $move['power'],
            $move['energy'],
            $move['energyGain'],
            $move['cooldown'],
            $move['buffs'][0] ?? null,
            $move['buffs'][1] ?? null,
        ]));
    }
}
