<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\FlightStatus;
use App\Repository\FlightRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: FlightRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['flight:read']]),
        new GetCollection(normalizationContext: ['groups' => ['flight:read']])
        // No POST/PUT/PATCH/DELETE for now – managed by admin or scheduling logic
    ]
)]
class Flight
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['flight:read', 'booking:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'flights')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['flight:read', 'booking:read'])]
    private ?Routes $route = null;

    #[ORM\ManyToOne(inversedBy: 'flights')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['flight:read', 'booking:read'])]
    private ?Aircraft $aircraft = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['flight:read', 'booking:read'])]
    private ?\DateTimeInterface $departureTime = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['flight:read', 'booking:read'])]
    private ?\DateTimeInterface $arrivalTime = null;

    #[ORM\Column(type: 'string', enumType: FlightStatus::class)]
    #[Groups(['flight:read', 'booking:read'])]
    private FlightStatus $status = FlightStatus::Scheduled;

    #[ORM\Column(type: 'integer')]
    #[Groups(['flight:read', 'booking:read'])] // ✅ Read-only: Modified via processor
    private int $availableSeats = 0;

    #[ORM\OneToMany(mappedBy: 'flight', targetEntity: Booking::class)]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    // --- Getters & Setters ---

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getRoute(): ?Routes 
    { 
        return $this->route; 
    }

    public function setRoute(?Routes $route): static 
    { 
        $this->route = $route; 
        return $this; 
    }

    public function getAircraft(): ?Aircraft 
    { 
        return $this->aircraft; 
    }

    public function setAircraft(?Aircraft $aircraft): static 
    { 
        $this->aircraft = $aircraft; 
        return $this; 
    }

    public function getDepartureTime(): ?\DateTimeInterface 
    { 
        return $this->departureTime; 
    }

    public function setDepartureTime(\DateTimeInterface $departureTime): static 
    { 
        $this->departureTime = $departureTime; 
        return $this; 
    }

    public function getArrivalTime(): ?\DateTimeInterface 
    { 
        return $this->arrivalTime; 
    }

    public function setArrivalTime(\DateTimeInterface $arrivalTime): static 
    { 
        $this->arrivalTime = $arrivalTime; 
        return $this; 
    }

    public function getStatus(): FlightStatus 
    { 
        return $this->status; 
    }

    public function setStatus(FlightStatus $status): static 
    { 
        $this->status = $status; 
        return $this; 
    }

    public function getAvailableSeats(): int 
    { 
        return $this->availableSeats; 
    }

    public function setAvailableSeats(int $availableSeats): static 
    { 
        $this->availableSeats = $availableSeats; 
        return $this; 
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setFlight($this);
        }
        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            if ($booking->getFlight() === $this) {
                $booking->setFlight(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        $origin = $this->route?->getOrigin() ?? 'Unknown';
        $destination = $this->route?->getDestination() ?? 'Unknown';
        $time = $this->departureTime?->format('Y-m-d H:i') ?? 'No time';

        return sprintf('%s → %s @ %s', $origin, $destination, $time);

    }

    public function getBookedSeatList(): string
    {
        $seats = [];

        foreach ($this->getBookings() as $booking) {
            foreach ($booking->getPassengers() as $passenger) {
                $seats[] = $passenger->getSeatNumber();
            }
        }

        return empty($seats)
            ? '<i>No seats booked</i>'
            : implode(', ', $seats);
    }

    public function getSeatMapHtml(): string
    {
        $aircraft = $this->getAircraft();
        if (!$aircraft) {
            return '<p><em>No aircraft assigned.</em></p>';
        }

        $capacity = $aircraft->getCapacity();
        $seatLetters = ['A', 'B', 'C', 'D'];
        $rows = (int) ceil($capacity / 4);

        // Build full seat list
        $allSeats = [];
        for ($i = 1; $i <= $rows; $i++) {
            foreach ($seatLetters as $letter) {
                $allSeats[] = $i . $letter;
            }
        }

        // Split seats by class
        $businessCount = (int) ceil(count($allSeats) * 0.2);
        $businessSeats = array_slice($allSeats, 0, $businessCount);
        $economySeats = array_slice($allSeats, $businessCount);

        // Gather booked seats
        $booked = [];
        foreach ($this->bookings as $booking) {
            foreach ($booking->getPassengers() as $passenger) {
                $booked[$passenger->getSeatNumber()] = true;
            }
        }

        // Legend
        $html = '<div style="font-family: monospace;">';
        $html .= '<p>';
        $html .= '<span style="display:inline-block;width:20px;height:20px;background:#007bff;margin-right:5px;"></span> Business &nbsp; ';
        $html .= '<span style="display:inline-block;width:20px;height:20px;background:#28a745;margin-right:5px;"></span> Economy &nbsp; ';
        $html .= '<span style="display:inline-block;width:20px;height:20px;background:#dc3545;margin-right:5px;"></span> Booked';
        $html .= '</p>';

        // Render seat grid
        for ($i = 1; $i <= $rows; $i++) {
            foreach ($seatLetters as $index => $letter) {
                $seat = $i . $letter;

                if (isset($booked[$seat])) {
                    $color = '#dc3545'; // booked
                } elseif (in_array($seat, $businessSeats)) {
                    $color = '#007bff'; // business
                } else {
                    $color = '#28a745'; // economy
                }

                // Add aisle after B
                $marginRight = ($index === 1) ? 'margin-right: 30px;' : '';

                $html .= "<span style=\"display:inline-block;width:30px;height:30px;line-height:30px;margin:2px;$marginRight;text-align:center;background:$color;color:white;border-radius:4px;\">$seat</span>";
            }
            $html .= '<br>';
        }

        $html .= '</div>';
        return $html;
    }

    public function getAllPassengers(): array
    {
        $all = [];
        foreach ($this->getBookings() as $booking) {
            foreach ($booking->getPassengers() as $passenger) {
                $all[] = $passenger;
            }
        }
        return $all;
    }


}
