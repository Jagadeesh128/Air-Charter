<?php

namespace App\Controller\Admin\CrudControllers;

use App\Entity\Passenger;
use App\Service\BookingManager;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use App\Controller\Admin\CrudControllers\FlightCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PassengerCrudController extends AbstractCrudController
{
    public function __construct(private BookingManager $bookingManager, private RequestStack $requestStack)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Passenger::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('booking'),
            TextField::new('name'),
            IntegerField::new('age'),
            TextField::new('documentId'),
            TextField::new('seatNumber')
                ->onlyOnDetail(),
            TextField::new('seatClass', 'Seat Class')
                // ->onlyOnDetail()
                // ->formatValue(function ($value, $entity) {
                //     return $entity->getBooking()?->getSeatClass() ?? 'N/A';
                // })
                ,
            TextField::new('checkInCode')->onlyOnIndex(), // or remove onlyOnIndex to show everywhere
            BooleanField::new('isCheckedIn')
                // ->onlyOnIndex()
                ->renderAsSwitch(false)
                ->formatValue(fn ($v) => $v ? '✅' : '❌'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $checkIn = Action::new('checkIn', 'Check In')
            ->linkToCrudAction('checkInAction')
            ->displayIf(fn (Passenger $p) => !$p->isCheckedIn());

        return $actions
            ->add(Crud::PAGE_DETAIL, $checkIn)
            ->add(Crud::PAGE_INDEX, $checkIn)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function checkInAction(AdminContext $context): Response
    {
        $passenger = $context->getEntity()->getInstance();
        $flight = $passenger->getBooking()->getFlight();
        $seatClass = $passenger->getSeatClass();

        $allSeats = $this->bookingManager->generateSeatMap($flight->getAircraft()->getCapacity());
        $seatMap = $this->bookingManager->splitSeatClasses($allSeats);
        $bookedSeats = $this->bookingManager->getBookedSeats($flight);
        $freeSeats = array_values(array_diff($seatMap[$seatClass], $bookedSeats));

        $request = $this->requestStack->getCurrentRequest();
        $availableSeats = $this->bookingManager->getAvailableSeatsByClass($flight, $passenger->getSeatClass());

        // ✅ Handle seat submission manually
        if ($request->isMethod('POST')) {
            $selectedSeat = $request->request->get('seat');

            if (!$selectedSeat) {
                $this->addFlash('danger', 'Please select a seat before confirming check-in.');
            } elseif (!in_array($selectedSeat, $availableSeats)) {
                $this->addFlash('danger', 'Selected seat is not available.');
            } else {
                $this->bookingManager->assignSeatAtCheckIn($passenger, strtoupper($selectedSeat));
                $this->addFlash('success', "Passenger checked in with seat $selectedSeat.");
                return $this->redirect($this->generateUrl('admin', [
                    'crudControllerFqcn' => FlightCrudController::class,
                    'crudAction' => 'detail',
                    'entityId' => $flight->getId(),
                ]));
            }
        }
        $freeSeats = array_values(array_diff($seatMap[$seatClass], $bookedSeats));
        return $this->render('admin/checkIn/passenger_checkin.html.twig', [
            'passenger' => $passenger,
            'availableSeats' => $availableSeats,
        ]);
    }
}
