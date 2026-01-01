<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PvPRanking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PvPRanking>
 */
class PvPRankingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PvPRanking::class);
    }
}
