<?php

namespace App\Entity;

use App\Repository\FolderRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FolderRepository::class)]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('VIEW', object) or is_granted('EDIT', object)"),
        new GetCollection(
			normalizationContext: [
				"enable_max_depth" => true,
				"groups" => ["folder:read:collection"]
			]
		),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER') and (is_granted('EDIT', object) or object.getOwner() == user)")
    ],
)]
class Folder
{
	#[Groups(["folder:read:collection"])] //Get a group to that parameter, so that It can be send
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(options: ["default" => 0])]
	private ?int $imageCount = 0;

    #[ORM\ManyToOne(inversedBy: 'folders')]
    private ?User $owner = null;

	#[Groups(["folder:read:collection"])]
	#[MaxDepth(1)]
	#[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'folders')]
	private Collection $rolesAllowed;

	#[Groups(["folder:read:collection"])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $eventDate = null;

	#[Groups(["folder:read:collection"])]
	#[MaxDepth(1)]
    #[ORM\ManyToMany(targetEntity: Media::class, inversedBy: 'folders')]
    private Collection $medias;

    public function __construct()
    {
        $this->rolesAllowed = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

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

    public function getImageCount(): ?int
    {
        return $this->imageCount;
    }

    public function setImageCount(int $imageCount): static
    {
        $this->imageCount = $imageCount;

        return $this;
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

    /**
     * @return Collection<int, Role>
     */
    public function getRolesAllowed(): Collection
    {
        return $this->rolesAllowed;
    }

    public function addRolesAllowed(Role $rolesAllowed): static
    {
        if (!$this->rolesAllowed->contains($rolesAllowed)) {
            $this->rolesAllowed->add($rolesAllowed);
        }

        return $this;
    }

    public function removeRolesAllowed(Role $rolesAllowed): static
    {
        $this->rolesAllowed->removeElement($rolesAllowed);

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->eventDate;
    }

    public function setEventDate(?\DateTimeInterface $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        $this->medias->removeElement($media);

        return $this;
    }

	public function __toString()
	{
		return $this->name;
	}
}
