<?php

namespace App\Twig\Runtime;

use App\Entity\UserPvPPokemon;
use Twig\Extension\RuntimeExtensionInterface;

class PvPExtensionRuntime implements RuntimeExtensionInterface
{
    public function borderColor(int $rank): string
    {
        if ($rank === 0) {
            return 'border-2 border-gray-500 bg-slate-600';
        }

        $classes = 'border-2 ';
        $classMap = [
            1 => 'border-green-500 bg-green-700',
            10 => 'border-yellow-500 bg-yellow-700',
            30 => 'border-red-500 bg-red-700',
            100 => 'border-white bg-slate-600',
        ];

        foreach ($classMap as $maxRank => $class) {
            if ($rank <= $maxRank) {
                return $classes . $class;
            }
        }

        return '';
    }
}
