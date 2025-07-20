<?php
namespace App\Controller;

use App\Entity\Booking;
use App\Enum\BookingStatus;
use App\Service\BookingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class BookingCancelController
{
    public function __construct(private EntityManagerInterface $em, private BookingManager $bookingManager) {}

    public function __invoke(Booking $data): JsonResponse
    {
        $this->bookingManager->cancelBooking($data);

        return new JsonResponse([
            'message' => 'Booking cancelled successfully.',
            'status' => $data->getStatus()
        ]);
    }
}
