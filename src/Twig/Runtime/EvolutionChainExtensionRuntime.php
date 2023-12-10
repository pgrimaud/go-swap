<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class EvolutionChainExtensionRuntime implements RuntimeExtensionInterface
{
    public function displayChain(int $evolutionChainId, array $evolutionChains, string $language): string
    {
        if (isset($evolutionChains[$evolutionChainId])) {
            $chain = '';
            foreach ($evolutionChains[$evolutionChainId] as $evolution) {
                $chain .= $evolution[$language === 'fr' ? 'french_name' : 'english_name'] . '|';
            }
            return strtolower(substr($chain, 0, -1));
        }

        return '';
    }
}
