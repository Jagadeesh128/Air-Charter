<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Flight;
use App\Entity\Routes;
use App\Entity\Booking;
use App\Entity\Aircraft;
use App\Entity\Passenger;
use App\Entity\RefreshToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use App\Controller\Admin\CrudControllers\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(UserCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin Panel')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Refresh Token', 'fas fa-square-binary', RefreshToken::class);
        yield MenuItem::linkToCrud('Aircrafts', 'fa fa-plane', Aircraft::class);
        yield MenuItem::linkToCrud('Routes', 'fa fa-route', Routes::class);
        yield MenuItem::linkToCrud('Bookings', 'fa fa-book', Booking::class);
        yield MenuItem::linkToCrud('Flight', 'fa fa-calendar-days', Flight::class);
        yield MenuItem::linkToCrud('Passengers', 'fas fa-user-friends', Passenger::class);
    }
}
