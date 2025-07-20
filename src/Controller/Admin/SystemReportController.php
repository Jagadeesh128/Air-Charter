<?php

namespace App\Controller\Admin;

use App\Repository\AircraftRepository;
use App\Repository\BookingRepository;
use App\Repository\FlightRepository;
use App\Repository\PassengerRepository;
use App\Repository\RoutesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystemReportController extends AbstractController
{
    #[Route('/admin/report', name: 'admin_system_report')]
    public function index(UserRepository $userRepo, BookingRepository $bookingRepo, FlightRepository $flightRepo, PassengerRepository $passengerRepo, RoutesRepository $routesRepo, AircraftRepository $aircraftRepo): Response
    {
        $totalUsers = $userRepo->count([]);
        $totalBookings = $bookingRepo->count([]);
        $cancelledBookings = $bookingRepo->count(['status' => 'CANCELLED']);
        $activeBookings = $bookingRepo->count(['status' => 'CONFIRMED']);
        $totalFlights = $flightRepo->count([]);
        $totalPassengers = $passengerRepo->count([]);
        $totalRoutes = $routesRepo->count([]);
        $totalAircrafts = $aircraftRepo->count([]);

        return $this->render('admin/report.html.twig', [
            'totalUsers' => $totalUsers,
            'totalBookings' => $totalBookings,
            'activeBookings' => $activeBookings,
            'cancelledBookings' => $cancelledBookings,
            'totalFlights' => $totalFlights,
            'totalPassengers' => $totalPassengers,
            'totalRoutes' => $totalRoutes,
            'totalAircrafts' => $totalAircrafts,
        ]);
    }
}
