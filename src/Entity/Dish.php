<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\DishRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\MaxDepth;

#[ORM\Entity(repositoryClass: DishRepository::class)]
#[ApiResource]
class Dish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: FoodItem::class, inversedBy: 'dishes')]
    #[MaxDepth(1)]
    private Collection $dishItems;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $totalPrice = null;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'dish')]
    private Collection $orders;

    public function __construct()
    {
        $this->dishItems = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, foodItem>
     */
    public function getDishItems(): Collection
    {
        return $this->dishItems;
    }

    public function addDishItem(FoodItem $dishItem): static
    {
        if (!$this->dishItems->contains($dishItem)) {
            $this->dishItems->add($dishItem);
        }

        return $this;
    }

    public function removeDishItem(FoodItem $dishItem): static
    {
        $this->dishItems->removeElement($dishItem);

        return $this;
    }

    public function setTotalPrice(string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->addDish($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            $order->removeDish($this);
        }

        return $this;
    }
}
