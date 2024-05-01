<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class CartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cart::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')->hideOnForm(),
            yield TextField::new('address'),
            yield NumberField::new('totalPrice'),
            
        ];
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
}
