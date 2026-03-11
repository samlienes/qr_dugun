<?php

namespace App\Entity;

use App\Repository\WeddingHallRepository;
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

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function setLatitude(float $latitude): self { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?float { return $this->longitude; }
    public function setLongitude(float $longitude): self { $this->longitude = $longitude; return $this; }
}
