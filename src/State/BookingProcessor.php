<?php

namespace App\State;

use App\Entity\Booking;
use App\Service\BookingManager;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BookingProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private EntityManagerInterface $em,
        private Security $security,
        private BookingManager $bookingManager,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Booking) {
            return $this->decorated->process($data, $operation, $uriVariables, $context);
        }

        $user = $this->security->getUser();
        if ($user !== null) {
            $data->setUser($user);
        }

        $this->bookingManager->processBooking($data);

        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }

}
