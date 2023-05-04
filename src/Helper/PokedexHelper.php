<?php

namespace App\Helper;

class PokedexHelper
{
    public const POKEDEX_NORMAL = 'normal';
    public const POKEDEX_SHINY = 'shiny';
    public const POKEDEX_LUCKY = 'lucky';
    public const POKEDEX_THREE_STARS = 'threeStars';

    public const POKEDEX =[
        self::POKEDEX_NORMAL,
        self::POKEDEX_SHINY,
        self::POKEDEX_LUCKY,
        self::POKEDEX_THREE_STARS,
    ];

    public static function exist(string $pokedex): bool
    {
        return in_array($pokedex, self::POKEDEX);
    }
}