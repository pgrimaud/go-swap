<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PvPQuestion;
use App\Entity\PvPQuiz;
use App\Entity\Type;
use App\Entity\TypeEffectiveness;
use App\Repository\TypeEffectivenessRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class PvPQuestionFactory
{
    const WEAK = 0;
    const STRONG = 1;

    const WEAK_OR_STRONG = [
        self::WEAK,
        self::STRONG,
    ];

    public function __construct(
        private EntityManagerInterface      $entityManager,
        private TypeRepository              $typeRepository,
        private TypeEffectivenessRepository $typeEffectivenessRepository,
    )
    {
    }

    public function __invoke(PvPQuiz $pvpQuiz): PvPQuestion
    {
        [$questionGenerated, $validAnswerTypes] = $this->generateQuestionAndValidAnswer();
        [$answers, $goodAnswer] = $this->generateAnswers($validAnswerTypes);

        $question = new PvPQuestion();
        $question->setPvpQuiz($pvpQuiz);
        $question->setQuestion($questionGenerated);
        $question->setAnswers($answers);
        $question->setValidAnswer($goodAnswer);

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }

    /**
     * @return array{string, TypeEffectiveness[]}
     */
    private function generateQuestionAndValidAnswer(): array
    {
        /** @var Type $type */
        $type = $this->typeRepository->getRandomType();

        $weakOrStrong = self::WEAK_OR_STRONG[rand(1, 1)];
        return match ($weakOrStrong) {
            //self::WEAK => $this->getWeakQuestion($type),
            self::STRONG => $this->getStrongQuestion($type),
        };
    }

    /**
     * @param TypeEffectiveness[] $correctAnswerTypeEffectivenesses
     * @return array
     */
    private function generateAnswers(array $correctAnswerTypeEffectivenesses): array
    {
        $weakType = $correctAnswerTypeEffectivenesses[array_rand($correctAnswerTypeEffectivenesses)];

        $correctAnswerTypes = [];
        foreach ($correctAnswerTypeEffectivenesses as $correctAnswerTypeEffectiveness) {
            /** @var Type $sourceType */
            $sourceType = $correctAnswerTypeEffectiveness->getSourceType();
            $correctAnswerTypes[] = $sourceType;
        }

        $randomTypes = $this->typeRepository->findRandomTypes($correctAnswerTypes);

        $goodAnswer = rand(1, 4);

        /** @var Type $weakTypeSourceType */
        $weakTypeSourceType = $weakType->getSourceType();
        $answers[$goodAnswer] = sprintf('#%s# %s',
            $weakTypeSourceType->getSlug(),
            $weakTypeSourceType->getName()
        );

        $randomTypeKey = 0;
        for ($i = 1; $i <= 4 ; $i++) {
            if (!isset($answers[$i])) {
                $answers[$i] = sprintf('#%s# %s',
                    $randomTypes[$randomTypeKey]->getSlug(),
                    $randomTypes[$randomTypeKey]->getName()
                );

                $randomTypeKey++;
            }
        }

        ksort($answers);

        return [$answers, $goodAnswer];
    }

    /**
     * @return array{string, TypeEffectiveness[]}
     */
    private function getStrongQuestion(Type $type): array
    {
        $weaknesses = $this->typeEffectivenessRepository->getWeakAgainst()[$type->getId()];

        $question = sprintf('Which type is strong against #%s# %s?', $type->getSlug(), $type->getName());

        return [$question, $weaknesses];
    }
}