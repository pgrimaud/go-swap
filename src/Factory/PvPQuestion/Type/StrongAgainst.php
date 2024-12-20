<?php

declare(strict_types=1);

namespace App\Factory\PvPQuestion\Type;

use App\Factory\PvPQuestion\AbstractPvPQuestion;
use App\Factory\PvPQuestion\PvPQuestionInterface;
use App\Entity\Type;

class StrongAgainst extends AbstractPvPQuestion implements PvPQuestionInterface
{
    public function getQuestion(): string
    {
        return sprintf(
            'Which type is strong against #%s# %s?',
            $this->type->getSlug(),
            $this->type->getName(),
        );
    }

    public function getValidAnswer(): Type
    {
        $this->validAnswerTypes = $this->typeEffectiveness->getVulnerableTo($this->type);
        $randomKey = array_rand($this->validAnswerTypes);

        return $this->validAnswerTypes[$randomKey];
    }
}