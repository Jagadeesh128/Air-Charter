<?php

namespace App\Entity;

use App\Repository\PassengerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PassengerRepository::class)]
// ❌ Passenger API is hidden (no GET, POST, PUT, DELETE)
#[\ApiPlatform\Metadata\ApiResource(
    operations: [], // disable all endpoints
)]
class Passenger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['passenger:read', 'booking:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['passenger:read', 'booking:read', 'booking:write'])]
    private string $name;

    #[ORM\Column]
    #[Groups(['passenger:read', 'booking:read', 'booking:write'])]
    private int $age;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['passenger:read', 'booking:read'])] // ✅ seat number is generated automatically
    private ?string $seatNumber = null;

    #[ORM\ManyToOne(inversedBy: 'passengers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Booking $booking = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?string $documentId = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $checkInCode = null;

    #[ORM\Column]
    private ?bool $isCheckedIn = null;

    // 👉 Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

    public function getSeatNumber(): ?string
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(?string $seatNumber): self
    {
        $this->seatNumber = $seatNumber ? strtoupper($seatNumber) : null;
        return $this;
    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function setBooking(?Booking $booking): self
    {
        $this->booking = $booking;
        return $this;
    }

    public function getSeatClass(): ?string
    {
        return $this->booking?->getSeatClass();
    }

    public function getDocumentId(): ?string
    {
        return $this->documentId;
    }

    public function setDocumentId(?string $documentId): static
    {
        $this->documentId = $documentId;

        return $this;
    }

    public function getCheckInCode(): ?string
    {
        return $this->checkInCode;
    }

    public function setCheckInCode(?string $checkInCode): static
    {
        $this->checkInCode = $checkInCode;

        return $this;
    }

    public function isCheckedIn(): ?bool
    {
        return $this->isCheckedIn;
    }

    public function setIsCheckedIn(bool $isCheckedIn): static
    {
        $this->isCheckedIn = $isCheckedIn;

        return $this;
    }
}
