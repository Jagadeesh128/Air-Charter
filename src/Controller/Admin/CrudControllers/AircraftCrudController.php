<?php

namespace App\Controller\Admin\CrudControllers;

use App\Entity\Aircraft;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{TextField, IntegerField, ImageField, ChoiceField, DateField, DateTimeField};

class AircraftCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Aircraft::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('createdAt', 'Created')
                ->setFormTypeOptions([
                    'disabled' => true,     // prevents editing
                    'required' => false,
                ]),
            DateTimeField::new('updatedAt', 'Last Updated')
                ->setFormTypeOptions([
                    'disabled' => true,     // prevents editing
                    'required' => false,
                ]),
            TextField::new('name'),
            TextField::new('model'),
            IntegerField::new('capacity'),
            IntegerField::new('rangeKm'),
            ChoiceField::new('status')
                ->setChoices([
                    'Active' => 'active',
                    'Maintenance' => 'maintenance',
                    'Inactive' => 'inactive',
                ]),
            // Image preview (read from DB)
            ImageField::new('image', 'Aircraft Image')
                ->setBasePath('/uploads/aircrafts')
                ->setUploadDir('public/uploads/aircrafts')
                ->setFormTypeOptions([
                    'disabled' => true,     // prevents editing
                    'allow_delete' => true, // enables delete
                    'required' => false,
                ]),

            // Upload field (handled by VichUploaderBundle)
            TextField::new('imageFile', 'Upload new image / Replace above image if needed!')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                ])
                ->onlyOnForms(),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
