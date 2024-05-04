<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FoodCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FoodCategoryRepository::class)]
#[ApiResource]
class FoodCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'foodCategory', targetEntity: FoodItem::class)]
    private Collection $foodItem;

    #[ORM\Column]
    private ?int $step = null;

    public function __construct()
    {
        $this->foodItem = new ArrayCollection();
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

    /**
     * @return Collection<int, foodItem>
     */
    public function getFoodItem(): Collection
    {
        return $this->foodItem;
    }

    public function addFoodItem(FoodItem $foodItem): static
    {
        if (!$this->foodItem->contains($foodItem)) {
            $this->foodItem->add($foodItem);
            $foodItem->setFoodCategory($this);
        }

        return $this;
    }

    public function removeFoodItem(FoodItem $foodItem): static
    {
        if ($this->foodItem->removeElement($foodItem)) {
            // set the owning side to null (unless already changed)
            if ($foodItem->getFoodCategory() === $this) {
                $foodItem->setFoodCategory(null);
            }
        }

        return $this;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(int $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function __toString()
	{
		return $this->name;
	}
}
