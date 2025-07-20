<?php

namespace App\Controller\Admin\CrudControllers;

use App\Entity\Routes;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class RoutesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Routes::class;
    }

    public function configureFields(string $pageName): iterable
    {
            return [
            IdField::new('id')->hideOnForm(),

            TextField::new('origin')
                ->setLabel('Origin')
                ->setRequired(true),

            TextField::new('destination')
                ->setLabel('Destination')
                ->setRequired(true),

            AssociationField::new('aircraft')
                ->setLabel('Aircraft')
                ->setRequired(true),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DELETE);
    }
}
