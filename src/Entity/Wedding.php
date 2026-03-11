<?php

namespace App\Entity;

use App\Repository\WeddingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeddingRepository::class)]
class Wedding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $weddingCode = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $weddingDate = null;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(targetEntity: Photo::class, mappedBy: 'wedding')]
    private Collection $photos; // İsimlendirmeyi daha mantıklı yaptım (photos)

    #[ORM\ManyToOne(targetEntity: WeddingHall::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?WeddingHall $weddingHall = null;

    #[ORM\ManyToOne(targetEntity: Contract::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Contract $activeContract = null;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getWeddingCode(): ?string { return $this->weddingCode; }
    public function setWeddingCode(string $weddingCode): self { $this->weddingCode = $weddingCode; return $this; }

    public function getWeddingDate(): ?\DateTimeImmutable { return $this->weddingDate; }
    public function setWeddingDate(\DateTimeImmutable $weddingDate): self { $this->weddingDate = $weddingDate; return $this; }

    public function getWeddingHall(): ?WeddingHall { return $this->weddingHall; }
    public function setWeddingHall(?WeddingHall $weddingHall): self { $this->weddingHall = $weddingHall; return $this; }

    public function getActiveContract(): ?Contract { return $this->activeContract; }
    public function setActiveContract(?Contract $activeContract): self { $this->activeContract = $activeContract; return $this; }

    /** @return Collection<int, Photo> */
    public function getPhotos(): Collection { return $this->photos; }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setWedding($this);
        }
        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getWedding() === $this) {
                $photo->setWedding(null);
            }
        }
        return $this;
    }
}
