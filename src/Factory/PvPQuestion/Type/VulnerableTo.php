<?php

declare(strict_types=1);

namespace App\Factory\PvPQuestion\Type;

use App\Entity\Type;
use App\Factory\PvPQuestion\AbstractPvPQuestion;
use App\Factory\PvPQuestion\PvPQuestionInterface;

class VulnerableTo extends AbstractPvPQuestion implements PvPQuestionInterface
{
    public function getQuestion(): string
    {
        return sprintf(
            'Which type is vulnerable to #%s# %s?',
            $this->type->getSlug(),
            $this->type->getName(),
        );
    }

    public function getValidAnswer(): Type
    {
        $this->validAnswerTypes = $this->typeEffectiveness->getStrongAgainst($this->type);
        $randomKey = array_rand($this->validAnswerTypes);

        return $this->validAnswerTypes[$randomKey];
    }
}