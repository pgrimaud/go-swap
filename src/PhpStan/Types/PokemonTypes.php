<?php

declare(strict_types=1);

namespace App\PhpStan\Types;

/**
 * @phpstan-type Pokemon array{
 *      dex: int,
 *      speciesName: string,
 *      speciesId: string,
 *      baseStats: array{atk: int, def: int, hp: int},
 *      types: list<string>,
 *      fastMoves: list<string>,
 *      chargedMoves: list<string>,
 *      eliteMoves?: list<string>,
 *      tags?: list<string>,
 *      defaultIVs: array{
 *          cp500: array{0: int, 1: int, 2: int, 3: int},
 *          cp1500: array{0: int, 1: int, 2: int, 3: int},
 *          cp2500: array{0: int, 1: int, 2: int, 3: int}
 *      },
 *      level25CP: int,
 *      buddyDistance: int,
 *      thirdMoveCost: int,
 *      released: bool,
 *      family: array{
 *          id: string,
 *          evolutions: list<string>
 *      }
 *  }
 */
final class PokemonTypes
{
}
