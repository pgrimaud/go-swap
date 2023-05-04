<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class ButtonExtensionRuntime implements RuntimeExtensionInterface
{
    public function randomButton(): string
    {
        $from = [
            'from-pink-400 to-purple-600',
            'from-green-400 to-purple-600',
            'from-pink-400 to-green-600',
            'from-green-400 to-purple-600',
        ];

        return $from[array_rand($from)];
    }
}
