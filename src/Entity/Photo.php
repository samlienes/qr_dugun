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

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: true)] // Düğün bilgisi
    private ?Wedding $wedding = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: true)] // Fotoğrafı yükleyen kullanıcı
    private ?User $appUser = null;

    public function __construct() {
        $this->uploadedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }
    public function getUploadedAt(): ?\DateTimeImmutable { return $this->uploadedAt; }
    public function getWedding(): ?Wedding { return $this->wedding; }
    public function setWedding(?Wedding $wedding): static { $this->wedding = $wedding; return $this; }
    public function getAppUser(): ?User { return $this->appUser; }
    public function setAppUser(?User $appUser): static { $this->appUser = $appUser; return $this; }
}
