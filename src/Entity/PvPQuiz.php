<?php

namespace App\Entity;

use App\Repository\PvPQuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PvPQuizRepository::class)]
#[ORM\Table(name: 'pvp_quiz')]
class PvPQuiz
{
    const STATUS_STARTED = 'started';
    const STATUS_ENDED = 'ended';

    const NUMBER_OF_QUESTIONS = 10;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pvpQuizzes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = self::STATUS_STARTED;

    #[ORM\Column(nullable: true)]
    private ?int $grade = null;

    #[ORM\Column]
    private int $numberOfQuestions = self::NUMBER_OF_QUESTIONS;

    /**
     * @var Collection<int, PvPQuestion>
     */
    #[ORM\OneToMany(mappedBy: 'pvpQuiz', targetEntity: PvPQuestion::class, orphanRemoval: true)]
    private Collection $pvpQuestions;

    public function __construct()
    {
        $this->pvpQuestions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getGrade(): ?int
    {
        return $this->grade;
    }

    public function setGrade(?int $grade): static
    {
        $this->grade = $grade;

        return $this;
    }

    public function getNumberOfQuestions(): int
    {
        return $this->numberOfQuestions;
    }

    public function setNumberOfQuestions(int $numberOfQuestions): void
    {
        $this->numberOfQuestions = $numberOfQuestions;
    }

    /**
     * @return Collection<int, PvPQuestion>
     */
    public function getPvpQuestions(): Collection
    {
        return $this->pvpQuestions;
    }

    public function addPvpQuestion(PvPQuestion $pvpQuestion): static
    {
        if (!$this->pvpQuestions->contains($pvpQuestion)) {
            $this->pvpQuestions->add($pvpQuestion);
            $pvpQuestion->setPvpQuiz($this);
        }

        return $this;
    }

    public function removePvpQuestion(PvPQuestion $pvpQuestion): static
    {
        if ($this->pvpQuestions->removeElement($pvpQuestion)) {
            // set the owning side to null (unless already changed)
            if ($pvpQuestion->getPvpQuiz() === $this) {
                $pvpQuestion->setPvpQuiz(null);
            }
        }

        return $this;
    }

    public function hasToCreateQuestion(): bool
    {
        return $this->pvpQuestions->filter(function(PvPQuestion $question) {
            return $question->getStatus() === PvPQuestion::STATUS_ANSWERED;
        })->count() < $this->getNumberOfQuestions();
    }

    public function getLastUnansweredQuestion(): ?PvPQuestion
    {
        return $this->pvpQuestions->filter(function(PvPQuestion $question) {
            return $question->getStatus() === PvPQuestion::STATUS_CREATED;
        })->first() ?: null;
    }

    public function getAnsweredQuestions(): int
    {
        return $this->pvpQuestions->filter(function(PvPQuestion $question) {
            return $question->getStatus() === PvPQuestion::STATUS_ANSWERED;
        })->count();
    }

    public function calculateGrade(): void
    {
        $this->grade = $this->pvpQuestions->filter(function(PvPQuestion $question) {
            return (bool) $question->isGoodAnswer();
        })->count() / $this->getNumberOfQuestions() * 100;
    }
}
