<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PvPQuestion;
use App\Entity\PvPQuiz;
use App\Entity\Type;
use App\Factory\PvPQuestion\AbstractPvPQuestion;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TypeEffectiveness;

readonly class PvPQuestionFactory
{
    const QUESTIONS_CLASSES = [
        'StrongAgainst',
        'VulnerableTo',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TypeRepository $typeRepository,
        private TypeEffectiveness $typeEffectiveness
    ) {
    }

    public function __invoke(PvPQuiz $pvpQuiz): PvPQuestion
    {
        /** @var Type $type */
        $type = $this->typeRepository->getRandomType();
        // special case for normal type, no strength against
        if ($type->getSlug() === 'normal') {
            $classes =  array_diff(self::QUESTIONS_CLASSES, ['VulnerableTo']);
        } else {
            $classes = self::QUESTIONS_CLASSES;
        }

        $class = $classes[array_rand($classes)];

        $nameSpace = sprintf('App\Factory\PvPQuestion\Type\%s', $class);

        /** @var AbstractPvPQuestion $pvpQuestion */
        $pvpQuestion = new $nameSpace(
            $type,
            $this->typeEffectiveness,
            $this->typeRepository
        );

        $question = new PvPQuestion();
        $question->setPvpQuiz($pvpQuiz);
        $question->setQuestion($pvpQuestion->getQuestion());
        $question->setAnswers($pvpQuestion->getAnswers());
        $question->setValidAnswer($pvpQuestion->getValidAnswerChoice());

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        return $question;
    }
}