<?php
declare(strict_types=1);
namespace App\Controller\Admin\Field;

use App\Form\RolesType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;

final class RolesField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, $label = null): self
    {
		return (new self())
			->setProperty($propertyName)
			->setLabel($label)
			->setFormType(RolesType::class)
			->onlyOnForms()
		;
	}
}