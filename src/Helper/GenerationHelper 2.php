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



    public const GENERATION =[
        self::GENERATION_ONE => 'Kanto',
        self::GENERATION_TWO => 'Johto',
        self::GENERATION_THREE => 'Hoenn',
        self::GENERATION_FOUR => 'Sinnoh',
        self::GENERATION_FIVE => 'Unys',
        self::GENERATION_SIX => 'Kalos',
        self::GENERATION_SEVEN => 'Alola',
        self::GENERATION_EIGHT => 'Galar',
        self::GENERATION_NINE => 'Other',

    ];

    public static function exist(string $generation): bool
    {
        return isset(self::GENERATION[$generation]);
    }
}