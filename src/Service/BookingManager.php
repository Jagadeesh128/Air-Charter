<?php

namespace App\Service;

use App\Entity\Flight;
use App\Entity\Booking;
use App\Entity\Passenger;
use App\Enum\BookingStatus;
use App\Repository\PassengerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BookingManager
{
    public function __construct(
        private PassengerRepository $passengerRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function processBooking(Booking $booking): void
    {
        $flight = $booking->getFlight();
        $seatClass = $booking->getSeatClass();

        if (!$flight || !$flight->getAircraft()) {
            throw new BadRequestHttpException('Flight or aircraft not found.');
        }

        $submittedPassengers = $booking->getPassengers();
        if (count($submittedPassengers) > 0) {
            $passengerCount = count($submittedPassengers);
            $booking->setPassengerCount($passengerCount);

            foreach ($submittedPassengers as $passenger) {

                $passenger->setBooking($booking);
                $passenger->setIsCheckedIn(false);

                // ✅ Generate unique check-in code
                $passenger->setCheckInCode(strtoupper(bin2hex(random_bytes(4))));

                $this->em->persist($passenger);
            }
        } else {
            $passengerCount = $booking->getPassengerCount();

            for ($i = 1; $i <= $passengerCount; $i++) {
                $passenger = new Passenger();
                $passenger->setName("Passenger $i");
                $passenger->setAge(0);
                $passenger->setIsCheckedIn(false);
                $passenger->setBooking($booking);

                // ✅ Generate unique check-in code
                $passenger->setCheckInCode(strtoupper(bin2hex(random_bytes(4))));

                $booking->addPassenger($passenger);
                $this->em->persist($passenger);
            }
        }

        // ✅ Validate available seat count (per class)
        $availableByClass = $this->getAvailableSeatCountByClass($flight);
        if (($availableByClass[$seatClass] ?? 0) < $passengerCount) {
            $booking->setStatus(BookingStatus::PENDING); // Not enough seats
        } else {
            $booking->setStatus(BookingStatus::CONFIRMED);
            $flight->setAvailableSeats($flight->getAvailableSeats() - $passengerCount);

            $this->em->persist($booking);
            $this->em->persist($flight);
        }
    }

    public function assignSeatAtCheckIn(Passenger $passenger, ?string $preferredSeat = null): void
    {
        if ($passenger->isCheckedIn()) {
            throw new BadRequestHttpException("Passenger already checked in.");
        }

        $booking = $passenger->getBooking();
        $flight = $booking->getFlight();
        $seatClass = $passenger->getSeatClass();

        $allSeats = $this->generateSeatMap($flight->getAircraft()->getCapacity());
        $seatMap = $this->splitSeatClasses($allSeats);
        $bookedSeats = $this->passengerRepository->findSeatNumbersForFlight($flight);

        $availableSeats = array_values(array_diff($seatMap[$seatClass], $bookedSeats));

        if (empty($availableSeats)) {
            throw new BadRequestHttpException("No seats available in $seatClass class.");
        }

        $seat = null;

        if ($preferredSeat) {
            $preferredSeat = strtoupper(trim($preferredSeat));
            if (!in_array($preferredSeat, $seatMap[$seatClass], true)) {
                throw new BadRequestHttpException("Seat $preferredSeat is not in $seatClass class.");
            }
            if (in_array($preferredSeat, $bookedSeats, true)) {
                throw new BadRequestHttpException("Seat $preferredSeat is already booked.");
            }
            $seat = $preferredSeat;
        } else {
            $seat = array_shift($availableSeats);
        }

        $passenger->setSeatNumber($seat);
        $passenger->setIsCheckedIn(true);
        $this->em->persist($passenger);
        $this->em->flush();
    }

    public function generateSeatMap(int $capacity): array
    {
        $rows = (int) ceil($capacity / 4);
        $seats = [];
        $seatLetters = ['A', 'B', 'C', 'D'];

        for ($i = 1; $i <= $rows; $i++) {
            foreach ($seatLetters as $letter) {
                $seats[] = $i . $letter;
            }
        }

        return $seats;
    }

    public function splitSeatClasses(array $allSeats, float $businessPercent = 0.2): array
    {
        $businessCount = (int) ceil(count($allSeats) * $businessPercent);
        return [
            'business' => array_slice($allSeats, 0, $businessCount),
            'economy'  => array_slice($allSeats, $businessCount),
        ];
    }

    public function getAvailableSeatCountByClass(Flight $flight): array
    {
        $capacity = $flight->getAircraft()?->getCapacity() ?? 0;
        $allSeats = $this->generateSeatMap($capacity);
        $seatMap = $this->splitSeatClasses($allSeats);
        $bookedSeats = $this->passengerRepository->findSeatNumbersForFlight($flight);

        return [
            'business' => count(array_diff($seatMap['business'], $bookedSeats)),
            'economy'  => count(array_diff($seatMap['economy'], $bookedSeats)),
        ];
    }

    public function getAvailableSeatsByClass(Flight $flight, string $seatClass): array
    {
        $capacity = $flight->getAircraft()?->getCapacity() ?? 0;
        $allSeats = $this->generateSeatMap($capacity);
        $seatMap = $this->splitSeatClasses($allSeats);
        $bookedSeats = $this->passengerRepository->findSeatNumbersForFlight($flight);

        return array_values(array_diff($seatMap[$seatClass], $bookedSeats));
    }

    public function getBookedSeats(Flight $flight): array
    {
        return $this->passengerRepository->findSeatNumbersForFlight($flight);
    }

    public function cancelBooking(Booking $booking): void
    {
        // Avoid re-canceling
        if ($booking->getStatus() === BookingStatus::CANCELLED) {
            return;
        }

        $flight = $booking->getFlight();
        $passengers = $booking->getPassengers();

        // 1. Set status
        $booking->setStatus(BookingStatus::CANCELLED);

        // 2. Release seats and uncheck-in
        foreach ($passengers as $passenger) {
            if ($passenger->getSeatNumber()) {
                $passenger->setSeatNumber(null);
                $passenger->setIsCheckedIn(false);
                $this->em->persist($passenger);
            }
        }

        // 3. Increase available seats (if flight exists)
        if ($flight) {
            $flight->setAvailableSeats($flight->getAvailableSeats() + count($passengers));
            $this->em->persist($flight);
        }

        $this->em->persist($booking);
        $this->em->flush();
    }

}
