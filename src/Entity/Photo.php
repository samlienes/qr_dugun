<?php

namespace App\Entity;

use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'pending';

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)] // DÜZELTME: nullable false yapıldı
    private ?Wedding $wedding = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)] // DÜZELTME: nullable false yapıldı
    private ?AppUser $appUser = null;

    public function __construct() {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }

    public function getUploadedAt(): ?\DateTimeImmutable { return $this->uploadedAt; }

    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): static { $this->message = $message; return $this; }

    public function getWedding(): ?Wedding { return $this->wedding; }
    public function setWedding(?Wedding $wedding): static { $this->wedding = $wedding; return $this; }

    public function getAppUser(): ?AppUser { return $this->appUser; }
    public function setAppUser(?AppUser $appUser): static { $this->appUser = $appUser; return $this; }
}
