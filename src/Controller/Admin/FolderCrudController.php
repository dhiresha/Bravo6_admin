<?php

namespace App\Controller\Admin;

use App\Entity\Folder;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\RequestStack;

class FolderCrudController extends AbstractCrudController
{
	private RequestStack $requestStack;

    public function __construct(
		RequestStack $requestStack
	)
    {
		$this->requestStack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Folder::class;
    }

    public function configureFields(string $pageName): iterable
    {
		yield IdField::new('id')->hideOnForm();
		yield TextField::new('name');
        yield DateField::new('eventDate')
			->setRequired(true)
		;
		yield AssociationField::new('owner')->onlyOnIndex();
		yield AssociationField::new('rolesAllowed')->autocomplete();
		yield AssociationField::new('medias')->autocomplete();
		yield BooleanField::new('isPersonal', 'Personal')
			->onlyWhenCreating()
			->setFormTypeOption('mapped', false)
		;
    }

	public function configureActions(Actions $actions): Actions
	{
		$cancelAction = Action::new('index', 'Cancel', 'fa-solid fa-xmark')
			->linkToCrudAction('index')
			->setCssClass('btn btn-danger rounded-5 px-3 py-2')
		;

		return $actions
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action){
				return $action
					->setIcon('fa-solid fa-folder-plus')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
				return $action
					->setIcon('fa-solid fa-folder-plus')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
				return $action
					->setIcon('fa-solid fa-folder-plus')
					->setCssClass('rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
				return $action
					->setIcon('fa-solid fa-floppy-disk')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
				return $action
					->setIcon('fa-solid fa-floppy-disk')
					->setCssClass('rounded-5 px-3 py-2')
				;
			})
			->add(Crud::PAGE_NEW, $cancelAction)
			->add(Crud::PAGE_EDIT, $cancelAction)
		;
	}

	public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$request = $this->requestStack->getCurrentRequest();
		$currentUser = $this->getUser();
		$folderIsPersonal = $request->get('Folder')['isPersonal'] ?? false;
		$folderRoles = $request->get('Folder')['rolesAllowed'] ?? [];

		if ($folderIsPersonal || empty($folderRoles)) {
			$entityInstance->setOwner($currentUser);
		}

		$this->addFlash('success', 'Folder Created');
		$entityManager->persist($entityInstance);
		$entityManager->flush();
	}


    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $user = $this->getUser();
        $userRoles = $user->getRoles();

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->leftJoin('entity.rolesAllowed', 'ra')
            ->andWhere(
                $qb->expr()->orX(
                    'entity.owner = :user',
                    $qb->expr()->in('ra.code', ':userRoles')
                )
            )
            ->setParameter('user', $user)
            ->setParameter('userRoles', $userRoles);

        return $qb;
    }
}