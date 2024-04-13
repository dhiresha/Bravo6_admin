<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class RoleCrudController extends AbstractCrudController
{
	private $params;

    public function __construct(
		ParameterBagInterface $params,
	)
    {
        $this->params = $params;
    }

    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */

	public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$roleCodesNotAllowedToDelete = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
		$additionalRoleCodesNotAllowedToDelete = explode(',', $this->params->get('roles_not_allowed_to_delete'));

		// Merge the predefined role codes with the additional ones
		$allRoleCodesNotAllowedToDelete = array_merge($roleCodesNotAllowedToDelete, $additionalRoleCodesNotAllowedToDelete);

		// Check if the role code of the entityInstance is in the merged list
		if (in_array($entityInstance->getCode(), $allRoleCodesNotAllowedToDelete)) {
			// Redirect to index if deletion is not allowed
			$this->addFlash('danger', 'This role is not allowed to be deleted.');
		} else {
			$entityManager->remove($entityInstance);
			$entityManager->flush();
		}
	}
}
