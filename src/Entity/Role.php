<?php

namespace App\Entity;

use App\Repository\RolesRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolesRepository::class)]
#[ApiResource]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

	#[Groups(["folder:read:collection"])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

	#[Groups(["folder:read:collection"])]
    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\ManyToMany(targetEntity: Folder::class, mappedBy: 'rolesAllowed')]
	#[MaxDepth(1)]
    private Collection $folders;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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
            $folder->addRolesAllowed($this);
        }

        return $this;
    }

    public function removeFolder(Folder $folder): static
    {
        if ($this->folders->removeElement($folder)) {
            $folder->removeRolesAllowed($this);
        }

        return $this;
    }

	public function __toString()
	{
		return $this->name;
	}
}
