<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\QuizExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QuizExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('grade_color', [QuizExtensionRuntime::class, 'getGradeColor']),
            new TwigFunction('type_icon', [QuizExtensionRuntime::class, 'getTypeIcon']),
        ];
    }
}
