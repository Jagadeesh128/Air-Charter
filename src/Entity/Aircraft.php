<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RoutesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity]
#[ORM\Table(name: 'aircraft')]
#[ApiResource(
    operations: [
        new GetCollection(), // Anyone can view aircraft list
        new Get(),           // Anyone can view aircraft details
    ],
    normalizationContext: ['groups' => ['aircraft:read']],
    denormalizationContext: ['groups' => ['aircraft:write']]
)]
#[Vich\Uploadable]
#[HasLifecycleCallbacks]
class Aircraft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['aircraft:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['aircraft:read', 'aircraft:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['aircraft:read', 'aircraft:write'])]
    private ?string $model = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['aircraft:read', 'aircraft:write'])]
    private ?int $capacity = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['aircraft:read', 'aircraft:write'])]
    private ?int $rangeKm = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(['active', 'maintenance', 'inactive'])]
    #[Groups(['aircraft:read', 'aircraft:write'])]
    private ?string $status = 'active';

    #[ORM\Column(type: 'datetime')]
    #[Groups(['aircraft:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['aircraft:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['aircraft:read'])]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'aircraft_images', fileNameProperty: 'image')]
    #[Groups(['aircraft:write'])]
    private ?File $imageFile = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'aircraft')]
    private Collection $bookings;

    /**
     * @var Collection<int, Routes>
     */
    #[ORM\OneToMany(targetEntity: Routes::class, mappedBy: 'aircraft')]
    private Collection $routes;

    /**
     * @var Collection<int, Flight>
     */
    #[ORM\OneToMany(targetEntity: Flight::class, mappedBy: 'aircraft')]
    private Collection $flights;



    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->bookings = new ArrayCollection();
        $this->routes = new ArrayCollection();
        $this->flights = new ArrayCollection();
    }

    // === Getters and Setters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function getRangeKm(): ?int
    {
        return $this->rangeKm;
    }

    public function setRangeKm(int $rangeKm): self
    {
        $this->rangeKm = $rangeKm;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if ($imageFile !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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
            $this->bookings->add($booking);
            $booking->setAircraft($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAircraft() === $this) {
                $booking->setAircraft(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName(); // or getFullName(), or any unique user identifier
    }

    /**
     * @return Collection<int, Routes>
     */
    public function getRoutes(): Collection
    {
        return $this->routes;
    }

    public function addRoute(Routes $route): static
    {
        if (!$this->routes->contains($route)) {
            $this->routes->add($route);
            $route->setAircraft($this);
        }

        return $this;
    }

    public function removeRoute(Routes $route): static
    {
        if ($this->routes->removeElement($route)) {
            // set the owning side to null (unless already changed)
            if ($route->getAircraft() === $this) {
                $route->setAircraft(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flight>
     */
    public function getFlights(): Collection
    {
        return $this->flights;
    }

    public function addFlight(Flight $flight): static
    {
        if (!$this->flights->contains($flight)) {
            $this->flights->add($flight);
            $flight->setAircraft($this);
        }

        return $this;
    }

    public function removeFlight(Flight $flight): static
    {
        if ($this->flights->removeElement($flight)) {
            // set the owning side to null (unless already changed)
            if ($flight->getAircraft() === $this) {
                $flight->setAircraft(null);
            }
        }

        return $this;
    }
}
