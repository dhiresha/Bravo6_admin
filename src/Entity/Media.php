<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[Vich\Uploadable]
#[ApiResource]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(length: 255)]
	private ?string $fileType = null;

	#[Ignore]
	#[Vich\UploadableField(mapping: 'media_files', fileNameProperty: 'fileName', size: 'fileSize')]
	private ?File $file = null;

	#[ORM\Column(nullable: true)]
	private ?string $fileName = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(nullable: true)]
	private ?int $fileSize = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column(nullable: true)]
	private ?\DateTimeImmutable $updatedAt = null;

	#[Groups(["folder:read:collection"])]
	#[MaxDepth(1)]
	#[ORM\ManyToMany(targetEntity: Folder::class, mappedBy: 'medias')]
	private Collection $folders;

	#[Ignore]
	#[Groups(["folder:read:collection"])]
	#[MaxDepth(1)]
	#[ORM\ManyToOne(inversedBy: 'media')]
	private ?User $owner = null;

	#[Groups(["folder:read:collection"])]
	#[ORM\Column]
	private ?bool $starred = false;

    #[ORM\Column(nullable: true)]
    private ?array $additionalData = null;

    public function __construct()
    {
        $this->folders = new ArrayCollection();
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

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;

        return $this;
    }

	public function getFile(): ?File
	{
		return $this->file;
	}

	/**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $file
     */
    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
			$this->fileType = $file->getMimeType(); // Set the fileType based on the uploaded file's MIME type
        }
    }

	public function getFileName(): ?string
	{
		return $this->fileName;
	}

	public function setFileName(?string $fileName): void
	{
		$this->fileName = $fileName;
	}

	public function getFileSize(): ?int
	{
		return $this->fileSize;
	}

	public function setFileSize(?int $fileSize): void
	{
		$this->fileSize = $fileSize;
	}

	#[Groups(["folder:read:collection"])]
	public function getFormattedFileSize(): string
	{
		$bytes = $this->getFileSize();
		if ($bytes == 0) {
			return '0 Bytes';
		}
	
		$k = 1024;
		$sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$i = floor(log($bytes) / log($k));
	
		return sprintf("%.2f %s", $bytes / pow($k, $i), $sizes[$i]);
	}

	public function getUpdatedAt(): ?\DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
	{
		$this->updatedAt = $updatedAt;
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
            $folder->addMedia($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): static
    {
        if ($this->folders->removeElement($folder)) {
            $folder->removeMedia($this);
        }

        return $this;
    }

	public function __toString()
	{
		return $this->name;
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

    public function isStarred(): ?bool
    {
        return $this->starred;
    }

    public function setStarred(bool $starred): static
    {
        $this->starred = $starred;

        return $this;
    }

    public function getAdditionalData(): ?array
    {
        return $this->additionalData;
    }

    public function setAdditionalData(?array $additionalData): static
    {
        $this->additionalData = $additionalData;

        return $this;
    }
}
