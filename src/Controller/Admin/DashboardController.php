<?php

namespace App\Controller\Admin;


use App\Entity\Media;
use App\Entity\Folder;
use App\Entity\Role;
use App\Entity\User;
use App\Service\ImageService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
	private $adminUrlGenerator;
	private $imageService;

	public function __construct(
		AdminUrlGenerator $adminUrlGenerator,
		ImageService $imageService
	) {
		$this->adminUrlGenerator = $adminUrlGenerator;
		$this->imageService = $imageService;
	}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        $text = "Welcome to U.N.O!";
        return $this->render('admin/adminDashboard.html.twig', [
            'text' => $text
        ]);
    }

	public function configureAssets(): Assets
	{
		return parent::configureAssets()
			->addWebpackEncoreEntry('app')
			;
	}

	public function configureCrud(): Crud
	{
		return parent::configureCrud()
			->addFormTheme('@Dropzone/form_theme.html.twig')
		;
	}

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('')
			// set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
			->setFaviconPath('images/logos_favicon.png')
            ->renderContentMaximized()
			->generateRelativeUrls()
			;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

		if ($this->isGranted('ROLE_SUPER_ADMIN')){
			yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
			yield MenuItem::linkToCrud('Roles', 'fa-solid fa-genderless', Role::class);
		}
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        yield MenuItem::linkToCrud('Media', 'fa-solid fa-photo-film', Media::class);
        yield MenuItem::linkToCrud('Folder','fa-solid fa-folder', Folder::class);
    }

	public function configureUserMenu(UserInterface $user): UserMenu
    {
		$userId = $user->getId();
		$firstName = $user->getFirstName();
		$lastName = $user->getLastname();
		$username = $user->getUsername();
		$displayName = "No Display Name";
		$profilePicUrl = "";

		// Url Used for User Profile
		$urlToUserProfile = $this->adminUrlGenerator
			->setController(UserCrudController::class)
			->setAction('detail')
			->setEntityId($userId)
			->generateUrl();

		// Url used for User Profile Edit
		$urlToEditProfile = $this->adminUrlGenerator
		->setController(UserCrudController::class)
		->setAction('edit')
		->setEntityId($userId)
		->generateUrl();

		// Checks 
		if (!$username && !$firstName && !$lastName){
			if ($user->getEmail()){
				$displayName = $user->getEmail();
			}
		}

		if ($username){
			$displayName = $username;
		} else {
			if (!empty($firstName) && !empty($lastName)) {
				$displayName = $firstName . ' ' . $lastName;
			} else {
				if (!empty($firstName)) {
					$displayName = $firstName;
				}

				if (!empty($lastName)) {
					$displayName = $lastName;
				}
			}
		}

		// Get the url for profile pic
		if (!empty($user->getProfilePicImgName())){
			$profilePicUrl = $this->imageService->getThumbnailUrl($user->getProfilePicImgName(), 'user_thumbnail');
		}

        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($displayName)
            // use this method if you don't want to display the name of the user
            // ->displayUserName(false)

            // you can return an URL with the avatar image
            ->setAvatarUrl($profilePicUrl)
            // ->setAvatarUrl($user->getProfileImageUrl())
            // use this method if you don't want to display the user image
            // ->displayUserAvatar(false)
            // you can also pass an email address to use gravatar's service
            // ->setGravatarEmail($user->getEmail())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
				MenuItem::section('Profile'),
				MenuItem::linkToUrl('My Profile', 'fa fa-id-card', $urlToUserProfile),
				MenuItem::linkToUrl('Edit Profile', 'fa fa-user-cog', $urlToEditProfile),
                // MenuItem::linkToLogout('Logout', 'fa fa-sign-out'),
            ]);
    }
}
