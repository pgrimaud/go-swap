<?php

declare(strict_types=1);

namespace App\Contract\Trait;

use Doctrine\ORM\Mapping as ORM;

trait TimestampTrait
{
    #[ORM\Column]
    protected ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->setUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
