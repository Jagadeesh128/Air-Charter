<?php

namespace App\Controller\Admin\CrudControllers;

use App\Entity\Booking;
use App\Enum\BookingStatus;
use App\Service\BookingManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

class BookingCrudController extends AbstractCrudController
{
    public function __construct(
        private BookingManager $bookingManager,
        private AdminUrlGenerator $adminUrlGenerator,
        private RequestStack $requestStack
    ) {}

    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Booking) {
            return;
        }

        try {
            $this->bookingManager->processBooking($entityInstance);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
            // 🔴 Do not persist or redirect — stay on the form
            return;
        }

        // ✅ Only save if everything is fine
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
         if (!$entityInstance instanceof Booking) {
            return;
        }

        try {
            $this->bookingManager->processBooking($entityInstance);
        } catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
            $this->addFlash('danger', $e->getMessage());
            // 🔴 Prevent redirect and DB update
            return;
        }

        // ✅ Only update if valid
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        $passengerTable =AssociationField::new('passengers', 'Passengers')
            ->setTemplatePath('admin/booking/passenger_list.html.twig')
            ->onlyOnDetail()
        ;

        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('user')->autocomplete(),
            AssociationField::new('flight')->autocomplete(),
            ChoiceField::new('seatClass')->setChoices([
                'Economy' => 'economy',
                'Business' => 'business',
            ]),
            IntegerField::new('passengerCount')
                ->setHelp("Used only if you want to reserve booking's"),
            CollectionField::new('passengers')
                ->setEntryIsComplex(true)
                ->useEntryCrudForm()
                ->setFormType(CollectionType::class)
                ->setHelp('Optional: Add full details for each passenger'),
            ChoiceField::new('status')
                ->setChoices([
                    'Pending' => BookingStatus::PENDING,
                    'Confirmed' => BookingStatus::CONFIRMED,
                    'Cancelled' => BookingStatus::CANCELLED,
                ])
                ->renderAsBadges([
                    BookingStatus::PENDING->value => 'warning',
                    BookingStatus::CONFIRMED->value => 'success',
                    BookingStatus::CANCELLED->value => 'danger',
                ]),
            TextField::new('mobileNumber'),
            TextareaField::new('notes'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            $passengerTable,
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
