<?php

namespace App\Form;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getRolesChoices(),
            'multiple' => true,
            'expanded' => false,  // Set to false to render as a select box
			'row_attr' => [
				'data-ea-widget' => "ea-autocomplete"
			],
			'attr' => [
				'data-ea-widget' => "ea-autocomplete"
			]
        ]);
    }

    private function getRolesChoices(): array
    {
        $rolesRepo = $this->entityManager->getRepository(Role::class);
        $roles = $rolesRepo->findAll();
        
        $choices = [];
        foreach ($roles as $role) {
            $choices[$role->getName()] = $role->getName();
        }

        return $choices;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}