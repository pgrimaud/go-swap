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
        self::POKEDEX_LUCKY,
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

    public const POKEDEX_SCREENSHOT_MAPPING = [
        'PURIFIED' => self::POKEDEX_PURIFIED,
        'SHADOW' => self::POKEDEX_SHADOW,
        'PERFECT' => '', // not implemented yet
        '3 STARS' => self::POKEDEX_THREE_STARS,
        'SHINY 3 STARS' => '', // not implemented yet
        'SHINY' => self::POKEDEX_SHINY,
        'LUCKY' => self::POKEDEX_LUCKY,
        'EVENT' => '',
        'MEGA' => '', // not implemented yet
        'G-MAX' => '', // not implemented yet
        'ALL' => self::POKEDEX_NORMAL,
    ];

    public const POKEDEX_MAPPING_FIELD = [
        self::POKEDEX_NORMAL => 'normal',
        self::POKEDEX_SHINY => 'shiny',
        self::POKEDEX_LUCKY => 'lucky',
        self::POKEDEX_THREE_STARS => 'three_stars',
        self::POKEDEX_SHADOW => 'shadow',
        self::POKEDEX_PURIFIED => 'purified',
    ];

    public static function exist(string $pokedex): bool
    {
        return isset(self::POKEDEX[$pokedex]);
    }
}