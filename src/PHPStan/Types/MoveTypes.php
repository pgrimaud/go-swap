<?php

declare(strict_types=1);

namespace App\PHPStan\Types;

/**
 * @phpstan-type Move array{
 *     moveId: string,
 *     name: string,
 *     abbreviation?: string,
 *     type: string,
 *     power: int,
 *     energy: int,
 *     energyGain: int,
 *     cooldown: int,
 *     buffs?: array<int, int>,
 *     buffTarget?: string,
 *     buffApplyChance?: string,
 *     archetype: string|null
 * }
 */
final class MoveTypes
{
}
