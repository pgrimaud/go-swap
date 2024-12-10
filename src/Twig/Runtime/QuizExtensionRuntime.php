<?php

namespace App\Twig\Runtime;

use App\Entity\Type;
use App\Repository\TypeRepository;
use Twig\Extension\RuntimeExtensionInterface;

class QuizExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly TypeRepository $typeRepository)
    {

    }

    public function getGradeColor(int $value): string
    {
        if ($value <= 50) {
            return 'text-red-500';
        }
        if ($value < 90) {
            return 'text-orange-500';
        }
        if ($value <= 100) {
            return 'text-green-500';
        }

        return '';
    }

    public function getTypeIcon(string $text): string
    {
        preg_match('/#([^#]*)#/', $text, $matches);


        if (!isset($matches[1])) {
            return $text;
        }

        /** @var Type $type */
        $type = $this->typeRepository->findOneBy(['slug' => $matches[1]]);

        return str_replace(
            sprintf('#%s#', $matches[1]),
            sprintf('<img src="/images/type/%s" class="inline w-[25px]">', $type->getIcon()), $text
        );
    }
}
