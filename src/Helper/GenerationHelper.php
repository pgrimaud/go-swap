<?php

namespace App\Helper;

class GenerationHelper
{
    public const GENERATION_ONE = '1G';
    public const GENERATION_TWO = '2G';
    public const GENERATION_THREE = '3G';
    public const GENERATION_FOUR = '4G';
    public const GENERATION_FIVE = '5G';
    public const GENERATION_SIX = '6G';
    public const GENERATION_SEVEN = '7G';
    public const GENERATION_EIGHT = '8G';
    public const GENERATION_NINE = '9G';

    public const GENERATIONS = [
        self::GENERATION_ONE => 'Kanto (1-151)',
        self::GENERATION_TWO => 'Johto (152-251)',
        self::GENERATION_THREE => 'Hoenn (252-386)',
        self::GENERATION_FOUR => 'Sinnoh (387-493)',
        self::GENERATION_FIVE => 'Unys (494-649)',
        self::GENERATION_SIX => 'Kalos (650-721)',
        self::GENERATION_SEVEN => 'Alola (722-809)',
        self::GENERATION_EIGHT => 'Galar (810-898)',
        self::GENERATION_NINE => 'Paldea (899-1010)',
    ];

    public static function exist(string $generation): bool
    {
        return isset(self::GENERATIONS[$generation]);
    }
}