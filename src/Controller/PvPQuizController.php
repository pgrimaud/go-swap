<?php

namespace App\Controller;

use App\Entity\PvPQuestion;
use App\Entity\PvPQuiz;
use App\Entity\User;
use App\Factory\PvPQuestionFactory;
use App\Repository\PvPQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pvp/quiz')]
class PvPQuizController extends AbstractController
{
    #[Route('', name: 'app_pvp_quizzes')]
    public function quizzes(PvPQuizRepository $pvpQuizRepository): Response
    {
        return $this->render('pvp/quiz/index.html.twig', [
            'quizzes' => $pvpQuizRepository->findBy([
                'user' => $this->getUser()
            ], [
                'id' => 'DESC',
            ])
        ]);
    }

    #[Route('/question/submit/{id}', name: 'app_pvp_quiz_question_submit', methods: ['POST'])]
    public function submitAnswer(
        PvPQuestion            $question,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($question->getPvpQuiz()?->getUser()?->getId() !== $user->getId()) {
            throw new HttpException(403, 'You are not allowed to answer this question');
        }

        $data = $request->request->all();

        $answerId = (int)$data['answer'];
        $validAnswer = $answerId === $question->getValidAnswer();

        $question->setStatus(PvPQuestion::STATUS_ANSWERED);
        $question->setGoodAnswer($validAnswer);

        /** @var PvPQuiz $quiz */
        $quiz = $question->getPvpQuiz();

        // is lastQuestion ?
        if ($quiz->getAnsweredQuestions() === $quiz->getNumberOfQuestions()) {
            $quiz->setEndedAt(new \DateTimeImmutable());
            $quiz->setStatus(PvPQuiz::STATUS_ENDED);

            $quiz->calculateGrade();
            $entityManager->persist($quiz);
        }

        $entityManager->flush();

        return $this->json([
            'correct' => $validAnswer,
            'goodAnswer' => $question->getValidAnswer(),
        ]);
    }

    #[Route('/start', name: 'app_pvp_quiz_start')]
    public function startQuiz(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $quiz = new PvPQuiz();
        $quiz->setUser($user);
        $quiz->setStartedAt(new \DateTimeImmutable());

        $entityManager->persist($quiz);
        $entityManager->flush();

        return $this->redirectToRoute('app_pvp_quiz', ['id' => $quiz->getId()]);
    }

    #[Route('/{id}/results', name: 'app_pvp_quiz_result')]
    public function quizResult(PvPQuiz $quiz): Response
    {
        return $this->render('pvp/quiz/results.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}', name: 'app_pvp_quiz')]
    public function quiz(PvPQuiz $quiz, PvPQuestionFactory $pvpQuestionFactory): Response
    {
        // has already a question created
        $question = $quiz->getLastUnansweredQuestion();

        // generate question
        if ($quiz->hasToCreateQuestion() && !$question instanceof PvPQuestion) {
            $question = ($pvpQuestionFactory)($quiz);
        }

        if (!$question) {
            return $this->redirectToRoute('app_pvp_quiz_result', ['id' => $quiz->getId()]);
        }

        return $this->render('pvp/quiz/quiz.html.twig', [
            'currentQuestionNumber' => $quiz->getAnsweredQuestions() + 1,
            'quiz' => $quiz,
            'question' => $question,
        ]);
    }
}
