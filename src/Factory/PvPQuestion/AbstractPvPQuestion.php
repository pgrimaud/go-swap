<?php

declare(strict_types=1);

namespace App\Factory\PvPQuestion;

use App\Entity\Type;
use App\Repository\TypeRepository;
use App\Service\TypeEffectiveness;

abstract class AbstractPvPQuestion
{
    /**
     * @var Type[]
     */
    protected array $validAnswerTypes;

    /**
     * @var Type[]
     */
    protected array $wrongAnswerTypes;

    protected Type $validAnswerType;
    /**
     * @var Type[]|array
     */
    private array $answers;

    public function __construct(
        protected Type $type,
        protected TypeEffectiveness $typeEffectiveness,
        protected TypeRepository $typeRepository,
    ) {
        $this->validAnswerType = $this->getValidAnswer();
        $this->wrongAnswerTypes = $this->getWrongAnswers();

        $this->answers = array_merge([$this->validAnswerType], $this->wrongAnswerTypes);
    }

    protected function getWrongAnswers(): array
    {
        return $this->typeRepository->findRandomTypes($this->validAnswerTypes);
    }

    /**
     * @return string[]
     */
    public function getAnswers(): array
    {
        shuffle($this->answers);

        $results = [];
        foreach ($this->answers as $k => $answer) {
            $results[$k + 1] = sprintf('#%s# %s',
                $answer->getSlug(),
                $answer->getName()
            );
        }

        return $results;
    }

    public function getValidAnswerChoice(): int
    {
        return (int) (array_search($this->validAnswerType, $this->answers)) + 1;
    }

    abstract public function getQuestion(): string;

    abstract protected function getValidAnswer(): Type;
}