<?php

declare(strict_types=1);

namespace App\Helper;

class PokedexHelper
{
    public const POKEDEX_NORMAL = 'normal';
    public const POKEDEX_SHINY = 'shiny';
    public const POKEDEX_LUCKY = 'lucky';
    public const POKEDEX_THREE_STARS = 'threeStars';
    public const POKEDEX_SHADOW = 'shadow';
    public const POKEDEX_PURIFIED = 'purified';

    public const FILTERABLE_TYPES = [
        self::POKEDEX_SHINY,
        self::POKEDEX_SHADOW,
        self::POKEDEX_PURIFIED,
    ];

    public const POKEDEX = [
        self::POKEDEX_NORMAL => 'Normal',
        self::POKEDEX_SHINY => 'Shiny',
        self::POKEDEX_LUCKY => 'Lucky',
        self::POKEDEX_THREE_STARS => '3 Stars',
        self::POKEDEX_SHADOW => 'Shadow',
        self::POKEDEX_PURIFIED => 'Purified',
    ];

    public static function exist(string $pokedex): bool
    {
        return isset(self::POKEDEX[$pokedex]);
    }
}