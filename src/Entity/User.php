<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator\UniqueField;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[Gedmo\SoftDeleteable(fieldName: 'deleted_at', timeAware: false)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[HasLifecycleCallbacks]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: false)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 5, max: 100, minMessage: 'Should be greater than or equal {{ limit }} characters', maxMessage: 'Should be less than or equal {{ limit }} characters')]
    private ?string $firstname = null;

    #[ORM\Column(length: 180, nullable: false)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 5, max: 100, minMessage: 'Should be greater than or equal {{ limit }} characters', maxMessage: 'Should be less than or equal {{ limit }} characters')]
    private ?string $lastname = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    #[UniqueField(entityClass: User::class, field: "email", message: "This email is already taken.")]
    private ?string $email = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Shouldn\'t be empty')]
    #[Assert\Length(min:6, max:12, minMessage: 'Should be greater than or equal {{ limit }} characters', maxMessage: 'Should be less than or equal {{ limit }} characters')]
    #[Assert\Regex(pattern: '/^[a-z]+$/i', message: 'Invalid username pattern')]
    #[UniqueField(entityClass: User::class, field: "username", message: "This username is already taken.")]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'The role must be selected.')]
    #[Assert\NotNull(message: 'The role must be selected.')]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(groups: ['check-password'])]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['check-password'])]
    #[Assert\Expression(expression: "this.getPassword() === this.getConfirmPassword()", message: 'Password and Confirm Password Should be identical', groups: ['check-password'])]
    private ?String $confirmPassword = null;

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'owner')]
    private ?Collection $products = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $imageName;

    #[Vich\UploadableField(mapping: "user_image", fileNameProperty:"imageName")]
    #[Assert\Image(mimeTypes: ["image/jpg", "image/jpeg", "image/png"])]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $attachmentName;

    #[Vich\UploadableField(mapping: "user_attachment", fileNameProperty:"attachmentName")]
    #[Assert\Image(mimeTypes: ["application/pdf", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"])]
    private ?File $attachmentFile = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): static
    {
        $this->confirmPassword = $confirmPassword;

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

    public function setDeletedAt(\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        //$this->password = null;
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setOwner($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }

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
