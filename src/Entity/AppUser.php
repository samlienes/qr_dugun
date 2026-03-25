<?php

namespace App\Entity;

use App\Repository\AppUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: AppUserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phoneNumber'])]
class AppUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 12)]
    private ?string $firstName = null;

    #[ORM\Column(length: 12)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20)]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $verificationCode = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    // İZOLASYON İÇİN GEREKEN İLİŞKİ: Salon Yöneticisinin Sorumlu Olduğu Salon
    #[ORM\ManyToOne(targetEntity: WeddingHall::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeddingHall $weddingHall = null;

    #[ORM\ManyToMany(targetEntity: Wedding::class, inversedBy: 'participants')]
    private Collection $joinedWeddings;

    public function __construct()
    {
        $this->joinedWeddings = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $firstName): static { $this->firstName = $firstName; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $lastName): static { $this->lastName = $lastName; return $this; }

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(string $phoneNumber): static { $this->phoneNumber = $phoneNumber; return $this; }

    public function isVerified(): ?bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): static { $this->isVerified = $isVerified; return $this; }

    public function getVerificationCode(): ?string { return $this->verificationCode; }
    public function setVerificationCode(?string $verificationCode): static { $this->verificationCode = $verificationCode; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->phoneNumber;
    }

    public function eraseCredentials(): void { }

    // GETTER VE SETTER (Salon İçin)
    public function getWeddingHall(): ?WeddingHall { return $this->weddingHall; }
    public function setWeddingHall(?WeddingHall $weddingHall): self { $this->weddingHall = $weddingHall; return $this; }

    /**
     * @return Collection<int, Wedding>
     */
    public function getJoinedWeddings(): Collection
    {
        return $this->joinedWeddings;
    }

    public function addJoinedWedding(Wedding $wedding): static
    {
        if (!$this->joinedWeddings->contains($wedding)) {
            $this->joinedWeddings->add($wedding);
        }
        return $this;
    }

    public function removeJoinedWedding(Wedding $wedding): static
    {
        $this->joinedWeddings->removeElement($wedding);
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'phoneNumber' => $this->phoneNumber,
            'password' => $this->password,
            'roles' => $this->roles,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->phoneNumber = $data['phoneNumber'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->roles = $data['roles'] ?? [];
    }

    public function __toString(): string
    {
        return ($this->firstName ?? 'İsimsiz') . ' ' . ($this->lastName ?? 'Kullanıcı') . ' (' . ($this->phoneNumber ?? 'Tel Yok') . ')';
    }
}
