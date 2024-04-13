<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
	normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[Groups(["folder:read:collection"])]
                   #[ORM\Id]
                   #[ORM\GeneratedValue]
                   #[ORM\Column]
                   private ?int $id = null;

	#[Groups(["folder:read:collection", 'user:read', 'user:write'])]
                   #[ORM\Column(length: 180, unique: true)]
                   private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

	#[Groups(["folder:read:collection", 'user:read', 'user:write'])]
                   #[ORM\Column(length: 255, nullable: true)]
                   private ?string $firstName = null;

	#[Groups(["folder:read:collection", 'user:read', 'user:write'])]
                   #[ORM\Column(length: 255, nullable: true)]
                   private ?string $lastName = null;

	#[Groups(["folder:read:collection", 'user:read', 'user:write'])]
                   #[ORM\Column(length: 255, nullable: true)]
                   private ?string $userName = null;

	#[Groups(['user:read', 'user:write'])]
                   #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Folder::class)]
                   private Collection $folders;

	// NOTE: This is not a mapped field of entity metadata, just a simple property.
	#[Vich\UploadableField(mapping: 'profile_pics', fileNameProperty: 'profilePicImgName', size: 'profilePicImgSize')]
               	private ?File $profilePic = null;

	#[Groups(['user:read'])]
               	#[ORM\Column(nullable: true)]
               	private ?string $profilePicImgName = null;

	#[ORM\Column(nullable: true)]
               	private ?int $profilePicImgSize = null;

	#[ORM\Column(nullable: true)]
                   private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Media::class)]
    private Collection $media;

    public function __construct()
    {
        $this->folders = new ArrayCollection();
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstName;
    }

    public function setFirstname(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastName;
    }

    public function setLastname(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->userName;
    }

    public function setUsername(?string $userName): static
    {
        $this->userName = $userName;

        return $this;
    }

	public function getFullNameAndUsername() {
                                       		$firstName = $this->getFirstname();
                                       		$lastName = $this->getLastname();
                                       		$userName = $this->getUsername();
                                       
                                       		$fullNameAndUsername = [
                                       			'firstname' => $firstName,
                                       			'lastname' => $lastName,
                                       			'username' => $userName
                                       		];
                                       
                                       		return $fullNameAndUsername;
                                       	}

    /**
     * @return Collection<int, Folder>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): static
    {
        if (!$this->folders->contains($folder)) {
            $this->folders->add($folder);
            $folder->setOwner($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): static
    {
        if ($this->folders->removeElement($folder)) {
            // set the owning side to null (unless already changed)
            if ($folder->getOwner() === $this) {
                $folder->setOwner(null);
            }
        }

        return $this;
    }

	/**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $profilePic
     */
    public function setProfilePic(?File $profilePic = null): void
    {
        $this->profilePic = $profilePic;

        if (null !== $profilePic) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getProfilePic(): ?File
    {
        return $this->profilePic;
    }

    public function setProfilePicImgName(?string $profilePicImgName): void
    {
        $this->profilePicImgName = $profilePicImgName;
    }

    public function getProfilePicImgName(): ?string
    {
        return $this->profilePicImgName;
    }

    public function setProfilePicImgSize(?int $profilePicImgSize): void
    {
        $this->profilePicImgSize = $profilePicImgSize;
    }

    public function getProfilePicImgSize(): ?int
    {
        return $this->profilePicImgSize;
    }

    public function getFormattedProfilePicSize(): string
    {
        $bytes = $this->getProfilePicImgSize();
        if ($bytes == 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $i = floor(log($bytes) / log($k));

        return sprintf("%.2f %s", $bytes / pow($k, $i), $sizes[$i]);
    }
	
	public function getUpdatedAt(): \DateTimeImmutable
               	{
               		return $this->updatedAt;
               	}

	// To prevent errors when serializing the uploaded file
	public function __serialize(): array
                   {
                       return [
                           'id' => $this->id,
                           'email' => $this->email,
                           'password' => $this->password
                       ];
                   }

	public function __toString()
               	{
               		return $this->email;
               	}

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium)) {
            $this->media->add($medium);
            $medium->setOwner($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getOwner() === $this) {
                $medium->setOwner(null);
            }
        }

        return $this;
    }
}
