<?php

namespace App\Entity;

use App\Repository\PvPQuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PvPQuestionRepository::class)]
#[ORM\Table(name: 'pvp_question')]
class PvPQuestion
{
    const STATUS_CREATED = 'created';
    const STATUS_ANSWERED = 'answered';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $question = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $answers = [];

    #[ORM\Column]
    private ?int $validAnswer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $goodAnswer = null;

    #[ORM\ManyToOne(inversedBy: 'pvpQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PvPQuiz $pvpQuiz = null;

    #[ORM\Column(length: 255)]
    private ?string $status = self::STATUS_CREATED;

    #[ORM\Column]
    private ?int $userAnswer = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswers(): array
    {
        return $this->answers;
    }

    public function setAnswers(array $answers): static
    {
        $this->answers = $answers;

        return $this;
    }

    public function getValidAnswer(): ?int
    {
        return $this->validAnswer;
    }

    public function setValidAnswer(int $validAnswer): static
    {
        $this->validAnswer = $validAnswer;

        return $this;
    }

    public function isGoodAnswer(): ?bool
    {
        return $this->goodAnswer;
    }

    public function setGoodAnswer(?bool $goodAnswer): static
    {
        $this->goodAnswer = $goodAnswer;

        return $this;
    }

    public function getPvpQuiz(): ?PvPQuiz
    {
        return $this->pvpQuiz;
    }

    public function setPvpQuiz(?PvPQuiz $pvpQuiz): static
    {
        $this->pvpQuiz = $pvpQuiz;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getUserAnswer(): ?int
    {
        return $this->userAnswer;
    }

    public function setUserAnswer(int $userAnswer): static
    {
        $this->userAnswer = $userAnswer;

        return $this;
    }
}
