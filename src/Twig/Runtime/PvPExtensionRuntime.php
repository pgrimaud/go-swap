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
            1 => 'border-blue-500 bg-blue-700 text-white',
            10 => 'border-green-500 bg-green-700 text-white',
            30 => 'border-yellow-500 bg-yellow-700 text-white0',
            100 => 'border-red-500 bg-red-700 text-white',
            4096 => 'border-back bg-black text-white',
        ];

        foreach ($classMap as $maxRank => $class) {
            if ($rank <= $maxRank) {
                return $classes . $class;
            }
        }

        return '';
    }
}
