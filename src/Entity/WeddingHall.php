<?php

namespace App\Entity;

use App\Repository\WeddingHallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeddingHallRepository::class)]
class WeddingHall
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\OneToMany(mappedBy: 'weddingHall', targetEntity: WeddingRoom::class, orphanRemoval: true)]
    private Collection $rooms;

    // EKSİK OLAN VE EKLENEN KISIM: Salonun Düğünleri
    #[ORM\OneToMany(mappedBy: 'weddingHall', targetEntity: Wedding::class)]
    private Collection $weddings;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->weddings = new ArrayCollection(); // Koleksiyonu başlattık
    }

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

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return Collection<int, WeddingRoom>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(WeddingRoom $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setWeddingHall($this);
        }
        return $this;
    }

    public function removeRoom(WeddingRoom $room): self
    {
        if ($this->rooms->removeElement($room)) {
            if ($room->getWeddingHall() === $this) {
                $room->setWeddingHall(null);
            }
        }
        return $this;
    }

    // EKSİK OLAN VE EKLENEN KISIM: Düğünler için Getter/Setter
    /**
     * @return Collection<int, Wedding>
     */
    public function getWeddings(): Collection
    {
        return $this->weddings;
    }

    public function addWedding(Wedding $wedding): self
    {
        if (!$this->weddings->contains($wedding)) {
            $this->weddings->add($wedding);
            $wedding->setWeddingHall($this);
        }
        return $this;
    }

    public function removeWedding(Wedding $wedding): self
    {
        if ($this->weddings->removeElement($wedding)) {
            if ($wedding->getWeddingHall() === $this) {
                $wedding->setWeddingHall(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
