<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class WeddingRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Hangi Ana Tesise (WeddingHall) bağlı olduğu
    #[ORM\ManyToOne(targetEntity: WeddingHall::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?WeddingHall $weddingHall = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getWeddingHall(): ?WeddingHall { return $this->weddingHall; }
    public function setWeddingHall(?WeddingHall $weddingHall): self { $this->weddingHall = $weddingHall; return $this; }

    public function __toString(): string
    {
        return (string) $this->name;
    }}
