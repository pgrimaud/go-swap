<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\EvolutionChainExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EvolutionChainExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('display_chain', [EvolutionChainExtensionRuntime::class, 'displayChain']),
        ];
    }
}
