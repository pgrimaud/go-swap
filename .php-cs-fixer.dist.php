<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'yoda_style' => false,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
