<?php

namespace App\Controller\Admin\CrudControllers;

use App\Entity\Flight;
use App\Entity\Passenger;
use App\Enum\FlightStatus;
use App\Enum\BookingStatus;
use App\Service\BookingManager;
use App\Repository\FlightRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FlightCrudController extends AbstractCrudController
{
    private BookingManager $bookingManager;
    private EntityManagerInterface $em;

    public function __construct(BookingManager $bookingManager, EntityManagerInterface $em)
    {
        $this->bookingManager = $bookingManager;
        $this->em = $em;
    }

    public static function getEntityFqcn(): string
    {
        return Flight::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $checkInAll = Action::new('checkInAll', 'Check-In All')
            ->linkToRoute('admin_flight_checkin_all', fn(Flight $flight) => ['id' => $flight->getId()])
            ->addCssClass('btn btn-success');

        return $actions
            ->add(Crud::PAGE_DETAIL, $checkInAll)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    #[Route('/admin/flight/{id}/checkin-all', name: 'admin_flight_checkin_all')]
    public function checkInAllAction(int $id,BookingManager $bookingManager,FlightRepository $flightRepository,EntityManagerInterface $em): Response 
    {
            $flight = $flightRepository->find($id);
        if (!$flight) {
            throw $this->createNotFoundException('Flight not found.');
        }

        // Only passengers who are not yet checked-in
        $passengersToCheckIn = array_filter(
            $flight->getAllPassengers(),
            fn(Passenger $p) => !$p->isCheckedIn() && $p->getBooking()?->getStatus() !== BookingStatus::CANCELLED
        );

        // ✅ FIX: If no passengers left to check in, show proper message
        if (count($passengersToCheckIn) === 0) {
            $this->addFlash('warning', 'All passengers are already checked in.');
            return $this->redirectToRoute('admin_flight_detail', [
                'entityId' => $flight->getId(),
            ]);
        }

        $availableSeats = [
            'business' => $bookingManager->getAvailableSeatsByClass($flight, 'business'),
            'economy'  => $bookingManager->getAvailableSeatsByClass($flight, 'economy'),
        ];

        foreach ($passengersToCheckIn as $passenger) {
            $seatClass = $passenger->getSeatClass();

            // Auto-assign only if seat is not already assigned
            if ($passenger->getSeatNumber() === null && !empty($availableSeats[$seatClass])) {
                $seat = strtoupper(array_shift($availableSeats[$seatClass]));
                $passenger->setSeatNumber($seat);
            }

            $passenger->setIsCheckedIn(true);
            $em->persist($passenger);
        }

        $em->flush();

        $this->addFlash('success', 'All passengers were auto-checked in with seat assignments.');
        return $this->redirectToRoute('admin_flight_detail', [
            'entityId' => $flight->getId(),
        ]);

    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('route'),
            AssociationField::new('aircraft'),
            DateTimeField::new('departureTime'),
            DateTimeField::new('arrivalTime'),
            ChoiceField::new('status')
                ->setLabel('Status')
                ->setChoices([
                    'Scheduled' => FlightStatus::Scheduled,
                    'Completed' => FlightStatus::Delayed,
                    'Cancelled' => FlightStatus::Cancelled,
                ])
                ->renderAsBadges([
                    FlightStatus::Scheduled->value => 'success',
                    FlightStatus::Delayed->value => 'info',
                    FlightStatus::Cancelled->value => 'danger',
                ]),
            IntegerField::new('availableSeats'),
            // TextareaField::new('bookedSeatList', 'Booked Seats')
            //     ->onlyOnDetail()
            //     ->renderAsHtml(),
            Field::new('allPassengers', 'Passengers')
                ->onlyOnDetail()
                ->setTemplatePath('admin/flight/passenger_table.html.twig'),
            TextField::new('getBookedSeatList', 'Booked Seats')
                ->onlyOnDetail(),
            TextField::new('seatMapHtml', 'Seat Map')
                ->onlyOnDetail()
                ->renderAsHtml(),
        ];
    }
}
