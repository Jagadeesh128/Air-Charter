<?php

namespace App\Command;

use App\Entity\Flight;
use App\Entity\Routes;
use App\Repository\RoutesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:schedule-flights')]
class ScheduleFlightsCommand extends Command
{
    public function __construct(
        private readonly RoutesRepository $routesRepository,
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Auto-schedules flights for each route based on predefined time slots.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = (new \DateTimeImmutable())->setTime(0, 0);
        $scheduledTimes = ['08:00', '14:00', '20:00'];

        $routes = $this->routesRepository->findAll();

        foreach ($routes as $route) {
            $aircraft = $route->getAircraft();

            if (!$aircraft) {
                $output->writeln("⏭️ Skipping route {$route->getOrigin()} → {$route->getDestination()} — no aircraft assigned.");
                continue;
            }

            foreach ($scheduledTimes as $timeStr) {
                $departureImmutable = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $today->format('Y-m-d') . ' ' . $timeStr);

                if (!$departureImmutable) {
                    $output->writeln("❌ Invalid time format: $timeStr");
                    continue;
                }

                $departure = \DateTime::createFromImmutable($departureImmutable);
                $arrival = (clone $departure)->modify('+2 hours');

                // Prevent duplicates
                $existing = $this->em->getRepository(Flight::class)->findOneBy([
                    'route' => $route,
                    'departureTime' => $departure,
                ]);

                if ($existing) {
                    $output->writeln("⚠️ Flight already exists: {$route->getOrigin()} → {$route->getDestination()} at $timeStr");
                    continue;
                }

                $flight = new Flight();
                $flight->setRoute($route);
                $flight->setAircraft($aircraft);
                $flight->setDepartureTime($departure);
                $flight->setArrivalTime($arrival);
                $flight->setAvailableSeats($aircraft->getCapacity());

                $this->em->persist($flight);

                $output->writeln("✅ Scheduled: {$route->getOrigin()} → {$route->getDestination()} at $timeStr");
            }
        }

        $this->em->flush();
        $output->writeln("🎉 All flights scheduled successfully.");
        return Command::SUCCESS;
    }
}
