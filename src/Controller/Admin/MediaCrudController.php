<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Folder;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use Symfony\Component\Validator\Constraints\All;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Validator\Constraints\File;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use Doctrine\ORM\QueryBuilder;
use App\Manager\MediaManager;

class MediaCrudController extends AbstractCrudController
{
	private $requestStack;
	private MediaManager $mediaManager;

	public function __construct(
		RequestStack $requestStack,
		MediaManager $mediaManager
	)
	{
		$this->requestStack = $requestStack;
		$this->mediaManager = $mediaManager;
	}

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureFields(string $pageName): iterable
    {
		yield IdField::new('id')->hideOnForm();
		yield TextField::new('name')
			->hideWhenCreating()
		;
		yield ImageField::new('fileName', 'Media Preview')
			->setTemplatePath('admin/media_preview.html.twig')
			->setCustomOption('pageName', $pageName)
			->hideOnForm()
		;
		yield TextField::new('fileType', "Media Type")
			->hideOnForm();
		yield TextField::new('formattedFileSize', 'Size')->hideOnForm();
		yield DateField::new('updatedAt')->hideOnForm();
		yield AssociationField::new('folders')->autocomplete();
		yield AssociationField::new('owner')->hideOnForm();
		yield BooleanField::new('starred')->hideWhenCreating();

		// "uploadMedia" is used by the dropzone controller, so should use the same, even though label can be different
		yield Field::new('uploadMedia', 'Upload Media')
			->setFormType(DropzoneType::class)
			->setFormTypeOptions([
				'mapped' => false,
				'multiple' => true,
				'attr' => [
					'placeholder' => 'Drag and drop a file or click to browse',
					'data-controller' => 'dhdropzone'
				],
				'constraints' => [
					new All([
						'constraints' => [
							new File([
								'maxSize' => '5200M'
							])
						]
					])
				]
			])
			->onlyWhenCreating()
		;
    }

	public function configureAssets(Assets $assets): Assets
	{
		return $assets
			->addWebpackEncoreEntry('media')
		;
	}

	public function configureCrud(Crud $crud): Crud
	{
		return $crud
			->overrideTemplate('crud/new', 'admin/Media/custom_new.html.twig')
		;
	}

	public function configureActions(Actions $actions): Actions
	{
		$cancelAction = Action::new('index', 'Cancel', 'fa-solid fa-xmark')
			->linkToCrudAction('index')
			->setCssClass('btn btn-danger rounded-5 px-3 py-2')
		;

		return $actions
			->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
			->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action){
				return $action
					->setLabel('Upload Media')
					->setIcon('fa fa-upload')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
				return $action
					->setLabel('Upload')
					->setIcon('fa fa-upload')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action){
				return $action
					->setIcon('fa-solid fa-floppy-disk')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
				return $action
					->setIcon('fa-solid fa-floppy-disk')
					->setCssClass('btn btn-primary rounded-5 px-3 py-2')
				;
			})
			->add(Crud::PAGE_INDEX, Action::DETAIL)
			->add(Crud::PAGE_NEW, $cancelAction)
			->add(Crud::PAGE_EDIT, $cancelAction)
			->setPermission(Action::EDIT, 'ALLOW_EDIT')
			->setPermission(Action::DELETE, 'ALLOW_DELETE')
		;
	}
	
	// Used for Creation
	public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$request = $this->requestStack->getCurrentRequest();
		$uploadedFiles = $request->files->get('Media')['uploadMedia'];  // Adjust this path to match your form structure
		// Since were are using autocomplete for the folder selection,
		// it is added in the autocomplete array of the request stack
		$folderIds = $request->get('Media')['folders']['autocomplete'] ?? null;
		$folders = $folderIds ? $entityManager->getRepository(Folder::class)->findBy(['id' => $folderIds]) : [];

		// Get the current user to assign to the media
		$currentUser = $this->getUser();
		
		if ($uploadedFiles && count($uploadedFiles) > 0) {
			foreach ($uploadedFiles as $uploadedFile) {
				// It's also a good practice to check if the uploaded file is valid
				if ($uploadedFile && $uploadedFile->isValid()) {
					$additionalData = [];
					$mediaFile = new Media();
					$mediaFile->setFile($uploadedFile);

					// Get the original name of the file without the extension
					$originalName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
					$mediaFile->setName($originalName);

					// Associate the folders selected to the entity before persisting
					if(!empty($folders)){
						foreach($folders as $folder){
							$folder->addMedia($mediaFile);
						}
					}

					if (!empty($currentUser)) {
						$mediaFile->setOwner($currentUser);
					}

					// Persist the media entity 1st to let vich add file name
					$entityManager->persist($mediaFile);

					// Process the media 
					$mediaProcessingData = $this->mediaManager->processMedia($mediaFile);
					// set processed media array to additional data
					$additionalData = $mediaProcessingData;
					
					// if empty additional data, then just it null
					if (empty($additionalData)) {
						$additionalData = null;
					}

					$mediaFile->setAdditionalData($additionalData);

					// Persist again the media to make sure additional data is set
					$entityManager->persist($mediaFile);
				}
			}
		} else {
			// Handle if no uploaded file if needed
		}

		$entityManager->flush();
	}

	// Used on Update
	public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
	{
		$folders = $entityInstance->getFolders();
		foreach ($folders as $folderId => $folder) {
			$folder->addMedia($entityInstance);
		}
		$entityManager->persist($entityInstance);
		$entityManager->flush();
	}

	public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
	{
		$qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
		$qb->leftJoin('entity.folders', 'f')
		->andWhere('entity.owner = :user OR f.owner = :user')
		->setParameter('user', $this->getUser());

		// If you have roles to check
		$userRoles = $this->getUser()->getRoles();
		$qb->leftJoin('f.rolesAllowed', 'r')
		->orWhere('r.code IN (:roles)')
		->setParameter('roles', $userRoles);

		return $qb;
	}
}