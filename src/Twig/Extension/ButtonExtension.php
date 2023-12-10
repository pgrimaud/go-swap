<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\ButtonExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ButtonExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('random_button', [ButtonExtensionRuntime::class, 'randomButton']),
        ];
    }
}
