<?php

namespace App\Entity;

use App\Repository\WeddingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeddingRepository::class)]
class Wedding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $weddingCode = null;

    #[ORM\ManyToOne(inversedBy: 'weddings')]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeddingHall $weddingHall = null;

    // EKSİKLİĞİ GİDERİLEN İLİŞKİ (WeddingRoom)
    #[ORM\ManyToOne(targetEntity: WeddingRoom::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeddingRoom $weddingRoom = null;

    #[ORM\OneToMany(mappedBy: 'wedding', targetEntity: Photo::class, cascade: ['persist', 'remove'])]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getWeddingCode(): ?string
    {
        return $this->weddingCode;
    }

    public function setWeddingCode(?string $weddingCode): static
    {
        $this->weddingCode = $weddingCode;

        return $this;
    }

    public function getWeddingHall(): ?WeddingHall
    {
        return $this->weddingHall;
    }

    public function setWeddingHall(?WeddingHall $weddingHall): static
    {
        $this->weddingHall = $weddingHall;

        return $this;
    }

    // YENİ EKLENEN GETTER VE SETTER (WeddingRoom için)
    public function getWeddingRoom(): ?WeddingRoom
    {
        return $this->weddingRoom;
    }

    public function setWeddingRoom(?WeddingRoom $weddingRoom): static
    {
        $this->weddingRoom = $weddingRoom;

        return $this;
    }

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
            $photo->setWedding($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getWedding() === $this) {
                $photo->setWedding(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}
