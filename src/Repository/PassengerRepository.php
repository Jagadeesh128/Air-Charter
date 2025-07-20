<?php

namespace App\Repository;

use App\Entity\Passenger;
use App\Entity\Flight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Passenger>
 */
class PassengerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Passenger::class);
    }

    /**
     * Returns all seat numbers already booked for a given flight
     *
     * @param Flight $flight
     * @return string[] array of seat numbers (e.g., ["1A", "1B"])
     */
    public function findSeatNumbersForFlight(Flight $flight): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.seatNumber')
            ->join('p.booking', 'b')
            ->where('b.flight = :flight')
            ->setParameter('flight', $flight);

        return array_column($qb->getQuery()->getResult(), 'seatNumber');
    }
}
