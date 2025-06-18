<?php

declare(strict_types=1);

namespace App\Helper;

class GenerationHelper
{
    public const string GENERATION_KANTO = 'kanto';
    public const string GENERATION_JOHTO = 'johto';
    public const string GENERATION_HOENN = 'hoenn';
    public const string GENERATION_SINNOH = 'sinnoh';
    public const string GENERATION_UNOVA = 'unova';
    public const string GENERATION_KALOS = 'kalos';
    public const string GENERATION_ALOLA = 'alola';
    public const string GENERATION_GALAR = 'galar';
    public const string GENERATION_HISUI = 'hisui';
    public const string GENERATION_PALDEA = 'paldea';
    public const string GENERATION_UNIDENTIFIED = 'unidentified';

    //public const array GENERATIONS = [
    //    self::GENERATION_KANTO,
    //    self::GENERATION_JOHTO,
    //    self::GENERATION_HOENN,
    //    self::GENERATION_SINNOH,
    //    self::GENERATION_UNOVA,
    //    self::GENERATION_KALOS,
    //    self::GENERATION_ALOLA,
    //    self::GENERATION_GALAR,
    //    self::GENERATION_HISUI,
    //    self::GENERATION_PALDEA,
    //    self::GENERATION_UNIDENTIFIED,
    //];

    public static function get(int $number): string
    {
        if ($number <= 151) {
            return self::GENERATION_KANTO;
        } elseif ($number <= 251) {
            return self::GENERATION_JOHTO;
        } elseif ($number <= 386) {
            return self::GENERATION_HOENN;
        } elseif ($number <= 493) {
            return self::GENERATION_SINNOH;
        } elseif ($number <= 649) {
            return self::GENERATION_UNOVA;
        } elseif ($number <= 721) {
            return self::GENERATION_KALOS;
        } elseif (in_array($number, [808, 809])) {
            return self::GENERATION_UNIDENTIFIED;
        } elseif ($number <= 809) {
            return self::GENERATION_ALOLA;
        } elseif ($number <= 898) {
            return self::GENERATION_GALAR;
        } elseif ($number <= 905) {
            return self::GENERATION_HISUI;
        } else {
            return self::GENERATION_PALDEA;
        }
    }
}