<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\PvPExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PvPExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('border_color', [PvPExtensionRuntime::class, 'borderColor']),
        ];
    }
}
