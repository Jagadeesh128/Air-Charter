<?php

namespace App\Entity;

use App\Enum\BookingStatus;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use App\State\BookingProcessor;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookingRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\BookingCancelController;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(processor: BookingProcessor::class, security: "is_granted('ROLE_USER')"),
        new Delete(uriTemplate: '/bookings/{id}/cancel', controller: BookingCancelController::class)
    ],
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']]
)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?Flight $flight = null;

    #[ORM\Column(length: 20, enumType: BookingStatus::class)]
    #[Groups(['booking:read'])]
    private BookingStatus $status = BookingStatus::PENDING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['booking:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['booking:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(['economy', 'business'])]
    #[Groups(['booking:read', 'booking:write'])]
    private ?string $seatClass = 'economy';

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive]
    #[Groups(['booking:read'])]
    private int $passengerCount = 1;

    #[ORM\OneToMany(mappedBy: 'booking', targetEntity: Passenger::class, cascade: ['persist', 'remove'])]
    #[Groups(['booking:read', 'booking:write'])]
    private Collection $passengers;

    #[ORM\Column(length: 25, nullable: true)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?string $mobileNumber = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->passengers = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getFlight(): ?Flight
    {
        return $this->flight;
    }

    public function setFlight(?Flight $flight): static
    {
        $this->flight = $flight;
        return $this;
    }

    public function getStatus(): BookingStatus
    {
        return $this->status;
    }

    public function setStatus(BookingStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getSeatClass(): ?string
    {
        return $this->seatClass;
    }

    public function setSeatClass(string $seatClass): self
    {
        $this->seatClass = $seatClass;
        return $this;
    }

    public function getPassengerCount(): int
    {
        return $this->passengerCount;
    }

    public function setPassengerCount(int $passengerCount): self
    {
        $this->passengerCount = $passengerCount;
        return $this;
    }

    /**
     * @return Collection<int, Passenger>
     */
    public function getPassengers(): Collection
    {
        return $this->passengers;
    }

    public function addPassenger(Passenger $passenger): self
    {
        if (!$this->passengers->contains($passenger)) {
            $this->passengers[] = $passenger;
            $passenger->setBooking($this);
        }
        return $this;
    }

    public function removePassenger(Passenger $passenger): self
    {
        if ($this->passengers->removeElement($passenger)) {
            if ($passenger->getBooking() === $this) {
                $passenger->setBooking(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            'Booking #%d - %s - %s',
            $this->getId(),
            $this->getFlight()?->getAircraft()?->getName() ?? 'No Flight',
            $this->getUser()?->getFullName() ?? 'No User'
        );
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber(?string $mobileNumber): self
    {
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    #[ORM\PreRemove]
    public function releaseSeatsOnDelete(): void
    {
        if ($this->getStatus() !== BookingStatus::CANCELLED) {
            $flight = $this->getFlight();
            if ($flight) {
                $seatCount = count($this->getPassengers());
                $flight->setAvailableSeats($flight->getAvailableSeats() + $seatCount);
            }
        }
    }
}
