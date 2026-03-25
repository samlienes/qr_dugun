<?php

namespace App\Entity;

use App\Repository\UserContractRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserContractRepository::class)]
class UserContract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userContracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AppUser $appUser = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    // YENİ EKLEME: IP Adresi Alanı
    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    // YENİ EKLEME: Onay Tarihi Alanı
    #[ORM\Column]
    private ?\DateTimeImmutable $acceptedAt = null;

    public function getId(): ?int { return $this->id; }

    public function getAppUser(): ?AppUser { return $this->appUser; }
    public function setAppUser(?AppUser $appUser): static { $this->appUser = $appUser; return $this; }

    public function getContract(): ?Contract { return $this->contract; }
    public function setContract(?Contract $contract): static { $this->contract = $contract; return $this; }

    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }

    public function getAcceptedAt(): ?\DateTimeImmutable { return $this->acceptedAt; }
    public function setAcceptedAt(\DateTimeImmutable $acceptedAt): static { $this->acceptedAt = $acceptedAt; return $this; }
}
