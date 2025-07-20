<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * Finds all refresh tokens that are expired
     *
     * @param \DateTimeInterface $datetime
     * @return RefreshToken[]
     */
    public function findInvalid(\DateTimeInterface $datetime): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.valid < :now')
            ->setParameter('now', $datetime)
            ->getQuery()
            ->getResult();
    }
}
