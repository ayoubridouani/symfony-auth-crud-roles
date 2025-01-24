<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`products`')]
#[Gedmo\SoftDeleteable(fieldName: 'deleted_at', timeAware: false)]
#[HasLifecycleCallbacks]
#[Vich\Uploadable]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 100)]
    private ?string $name;

    #[ORM\Column(type: TypeS::TEXT)]
    #[Assert\Length(min: 5)]
    #[Assert\NotBlank]
    private ?string $description;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\GreaterThan(value: 1)]
    #[Assert\NotBlank]
    private ?float $price;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'owner_id')]
    #[Assert\NotNull(message: 'The owner must be selected.')]
    private ?User $owner = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $imageName;

    #[Vich\UploadableField(mapping: "product_image", fileNameProperty:"imageName")]
    #[Assert\Image(mimeTypes: ["image/jpg", "image/jpeg", "image/png"])]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $attachmentName;

    #[Vich\UploadableField(mapping: "product_attachment", fileNameProperty:"attachmentName")]
    #[Assert\Image(mimeTypes: ["application/pdf", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"])]
    private ?File $attachmentFile = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->setUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName ?? '';
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): self
    {
        if($imageFile)
            $this->updated_at = new \DateTimeImmutable();

        $this->imageFile = $imageFile;

        return $this;
    }

    public function getAttachmentName(): ?string
    {
        return $this->attachmentName ?? '';
    }

    public function setAttachmentName(?string $attachmentName): static
    {
        $this->attachmentName = $attachmentName;

        return $this;
    }

    public function getAttachmentFile(): ?File
    {
        return $this->attachmentFile;
    }

    public function setAttachmentFile(?File $attachmentFile = null): self
    {
        if($attachmentFile)
            $this->updated_at = new \DateTimeImmutable();

        $this->attachmentFile = $attachmentFile;

        return $this;
    }
}
