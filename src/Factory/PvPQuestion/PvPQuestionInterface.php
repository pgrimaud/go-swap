<?php

declare(strict_types=1);

namespace App\Factory\PvPQuestion;

use App\Entity\Type;

interface PvPQuestionInterface
{
    public function getQuestion(): string;
    public function getValidAnswer(): Type;
}