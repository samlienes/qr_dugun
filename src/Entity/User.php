<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /** @var list<string> */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column]
    private ?int $birthYear = null;
    #[ORM\Column]
    private ?bool $isVerified = false;
    #[ORM\Column(length: 6, nullable: true)]
    private ?string $verificationCode = null;
    #[ORM\OneToMany(targetEntity: UserContract::class, mappedBy: 'appUser')]
    private Collection $userContracts;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(targetEntity: Photo::class, mappedBy: 'relation')]
    private Collection $photos;

    public function __construct() {
        $this->userContracts = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $fullName): static { $this->fullName = $fullName; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(string $phone): static { $this->phone = $phone; return $this; }

    public function getBirthYear(): ?int { return $this->birthYear; }
    public function setBirthYear(int $birthYear): static { $this->birthYear = $birthYear; return $this; }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setRelation($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getRelation() === $this) {
                $photo->setRelation(null);
            }
        }

        return $this;
    }
}
