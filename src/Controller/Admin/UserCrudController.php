<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\Dropzone\Form\DropzoneType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
	private $entityManager;
	private $requestStack;
	private $passwordHasher;

	public function __construct(
		EntityManagerInterface $entityManager,
		RequestStack $requestStack,
		UserPasswordHasherInterface $passwordHasher,
	) {
		$this->entityManager = $entityManager;
		$this->requestStack = $requestStack;
		$this->passwordHasher = $passwordHasher;
	}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {

		yield IdField::new('id')->hideOnForm();
		yield EmailField::new('email');
		yield TextField::new('password')
			->hideOnIndex()
			->hideOnDetail()
			->setFormType(PasswordType::class)
			->setRequired($pageName == Crud::PAGE_NEW)
			->setFormTypeOptions([
				'empty_data' => '',
				'attr' => [
					'class' => 'rmt-password-type-input'
				],
				'row_attr' => [
					'class' => 'rmt-password-type'
				],
				'toggle' => true,
				'button_classes' => [
					'btn', 
					'p-0', 
					'shadow-none', 
					'border-1', 
					'border-top', 
					'border-bottom', 
					'border-end', 
					'rmt-toggle-password-btn'
				],
				'hidden_label' => null,
                'visible_label' => null,
				'use_toggle_form_theme' => false
			])
		;
		yield TextField::new('firstName');
		yield TextField::new('lastName');
		yield TextField::new('userName');
		yield ChoiceField::new('roles')
			->setChoices($this->getRolesChoices())
			->allowMultipleChoices()
			->autocomplete()
		;
		yield ImageField::new('profilePicImgName', 'Profile Pic')
			->setTemplatePath('admin/User/profilePicPreview.html.twig')
			->setCustomOption('pageName', $pageName)
			->hideOnForm()
		;
		yield Field::new('uploadMedia', 'Upload Profile Pic')
			->setFormType(DropzoneType::class)
			->setFormTypeOptions([
				'mapped' => false,
				'multiple' => false,
				'attr' => [
					'placeholder' => 'Drag and drop a file or click to browse',
					'data-controller' => 'dhdropzone'
				],
			])
			->onlyOnForms()
		;
    }

	public function configureActions(Actions $actions): Actions
	{
		return $actions
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			->setPermission(ACTION::INDEX, 'ROLE_SUPER_ADMIN')
			->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
		;
	}

	// Custom Redirect when saving entity based on user roles
	protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
	{
		$userRoles = $this->getUser()->getRoles();

		// These are the roles required to know whether after saving,
		// the user redirect to index of UserCrud
		$requiredRolesToIndex = ['ROLE_SUPER_ADMIN'];
		// Check if the user has one of the required roles to redirect to UserCrud index
		$hasRequiredRole = array_intersect($userRoles, $requiredRolesToIndex);
		$dashboardAdminUrl = $this->generateUrl('admin_index');

		if (empty($hasRequiredRole)) {	
			// dd("Should Redirect to Dashboard");
			return new RedirectResponse($dashboardAdminUrl);
		}

		return parent::getRedirectResponseAfterSave($context, $action);
	}

	// Used for Creation
	public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$request = $this->requestStack->getCurrentRequest();
		$uploadedFile = $request->files->get('User')['uploadMedia'];  // Adjust this path to match your form structure
		$passwordInFormData = $entityInstance->getPassword();
		
		if ($uploadedFile) {
			$profilePic = $uploadedFile;

			$entityInstance->setProfilePic($profilePic);
		}

		// If the event is a create event, hash the password and update the entity.
		$hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $passwordInFormData);
		$entityInstance->setPassword($hashedPassword);

		$this->addFlash('success', 'User Created');
		$entityManager->persist($entityInstance);
		$entityManager->flush();
	}

	// Used on Update
	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$request = $this->requestStack->getCurrentRequest();
		$passwordInFormData = $entityInstance->getPassword();
		$uploadedFile = $request->files->get('User')['uploadMedia'];  // Adjust this path to match your form structure
		$updateMessage = "User Updated";

		if ($uploadedFile) {
			$profilePic = $uploadedFile;

			$entityInstance->setProfilePic($profilePic);
			$updateMessage = $updateMessage . "<br>Profile Pic Updated";
		}

		if (empty($passwordInFormData)) {
			// If the password field in the form is left empty,
			// fetch the current password from the database,
			// and set it back on the entity to prevent it from being overwritten.
			$unitOfWork = $this->entityManager->getUnitOfWork();
			$originalEntityData = $unitOfWork->getOriginalEntityData($entityInstance);
			$passwordInDatabase = $originalEntityData["password"];
			$entityInstance->setPassword($passwordInDatabase);
		} else {
			// If a new password is provided in the form, hash it and update the entity.
			$hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $passwordInFormData);
			$entityInstance->setPassword($hashedPassword);
			$updateMessage = $updateMessage . "<br>User Password Updated";
		}
		$this->addFlash('success', $updateMessage);
		$entityManager->persist($entityInstance);
		$entityManager->flush();
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
    
}
