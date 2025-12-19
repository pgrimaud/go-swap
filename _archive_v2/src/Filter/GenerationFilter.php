<?php

declare(strict_types=1);

namespace App\Filter;

use App\Entity\UserPvPPokemon;

final class GenerationFilter
{
    /**
     * @param UserPvPPokemon[] $pokemon
     *
     * @return UserPvPPokemon[]
     */
    public static function userPvPPokemon(array $pokemon): array
    {
        return $pokemon;
    }
}
