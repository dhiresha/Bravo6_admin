## Installation Guide
1. Clone this project using the github link using `git clone`
2. Go into the project folder, using `cd bravo6_project`
3. Run `composer install`
4. Run `npm install`

## Creating Project Database and User using MySQL/Doctrine ORM
1. Create MySQL user `bravo6`
2. Update `env.local` to match user and DB changes
3. Create Database using `php bin/console doctrine:database:create`
4. Do migration using `php bin/console doctrine:migrations:migrate` to update database

## Lexik JWT Authentication Bundle ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
1. If error occurs during `composer install` associated with lexik Bundle and `ext-sodium`
	- Go into `php.ini` file for current php, uncomment `extension=sodium`
	- Try `composer install` again
2. Download OpenSSL: [Click Here](https://slproweb.com/products/Win32OpenSSL.html)
2. Generate SSL Keys using `php bin/console lexik:jwt:generate-keypair`

## Initialise Directories to be needed ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
- Run this command to initialise directories needed in the project: `php bin/console app:initialise-directories`

## Initialise app-key to be used to authenticate incoming requests ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
- Run this command to initialise app key/token: `php bin/console app:generate-app-key`
- If you are using an `.env.*` or want to define another route where the __app key/token__ is store, in your `.env` or `.env.local` or any other `.env.*` you are using:
	1. Make sure you define your custom path here, which will indicate where the key will be stored, and where the `ApiRequestListener` will search for the app key/token:
	```bash
	# APP KEY DIRECTORY
	APP_KEY_DIR=var/dh_app_tokens
	```
	2. ![Custom Badge](https://img.shields.io/badge/IMPORTANT-red.svg) Make sure the location is not being tracked by `git`, if so add it to your `.gitignore` if you know it won't cause any development issues 


## Compiling CSS/JS using Webpack for Tailwind
- Compile `css/js` using `npm run dev`
- For Production Server, use: `npm run build`

## Start the Symfony Server
1. Start the server by using either of the following:
	- `symfony server:start`, which run the server with logs in the terminal (stop server using `Ctrl` + `C`)
	- `symfony server:start -d`, will run the server without logs(stop server using `symfony server:stop`)
	
---

### Custom Helpers/Managers/Controllers/Services/Classes used in this project ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg):

#### Services: 
1. VideoService:
	- `public function getFramePictureAt(File $mediaFile, $useMiddleFrame = false, $framePosition = 1)`
	- `public function serveVideo(Media $media): Response`
	- `public function getMiddleFrameOfVideo($mediaFilePath)`
	- `public function getVideoUrl(Media $media): ?string`
2. ImageService:
	- `public function getImageUrl(string $imageName, string $filter = 'original'): ?string`
	- `public function getThumbnailUrl(string $imageName, string $filter = 'thumbnail'): ?string`
	- `public function applyFilter($image, $filter)`

#### Resolvers:
1. UnoResolver extends `ResolverInterface` with custom logic

#### Utils:
1. FileUtils:
	- `public static function createDirectories(array $directories, string $basePath, Filesystem $filesystem, OutputInterface $output)`
	- `public static function createDirectory(string $directoryPath, Filesystem $filesystem, OutputInterface $output)`
	- `public static function ensureDirectoryExists(string $path)`

#### Normalizers (*customize the resource sent to the client (add fields in JSON documents, encode codes, dates…)*):
1. MediaNormalizer implements `NormalizerInterface` 

#### Voters (*custom authorization logic*):
1. FolderVoter extends `Voter`
2. MediaVoter extends `Voter`

#### Managers: 
1. MediaManager:
	- `public function getMediaHelper()`
	- `public function getRepository()`
	- `public function processMedia(Media $media, $filter = 'thumbnail')`
	- `public function serveMedia(Media $media)`

#### Helpers:
1. MediaHelper:
	- `public function processMediaFile(Media $media, $filter = '')`
	- `public function serveMedia(Media $media)`
	- `public function applyFilter(Media $media, $filter = 'thumbnail')`

#### Extensions (*Access to the query builder to change the DQL query*):
1. CurrentUserFoldersExtensions implements `QueryCollectionExtensionInterface`

#### Controllers
1. DhMediaController:
	- `public function serveCachedImage(string $path, string $filter): Response`
	- `public function serveMedia(string $filename): Response`

---

### Useful Resources ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
#### Symfony
- Entities and ORM : [Click Here](https://symfony.com/doc/current/doctrine.html)
- Doctrine Migrations Bundle: [Click Here](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html)
- Symfony Form Types: [Click Here](https://symfony.com/doc/current/reference/forms/types.html)
	- FileType: [Click Here](https://symfony.com/doc/current/reference/forms/types/file.html)
- Symfony Services: [Click Here](https://symfony.com/doc/current/service_container.html)
- Symfony Security [Click Here](https://symfony.com/doc/current/security.html):
	- Role Hierarchy[Click Here](https://symfony.com/doc/current/security.html#hierarchical-roles):
	```yaml
	# security.yaml

	security:
    role_hierarchy:
        ROLE_SUPER_ADMIN: [ROLE_SUPER_ADMIN_PROJECT]
		ROLE_USER: [ROLE_USER_PROJECT]
	```
- Symfony Local Server: [Click Here](https://symfony.com/doc/current/setup/symfony_server.html)
- Symfony Event Listeners/Subscribers: [Click Here](https://symfony.com/doc/current/event_dispatcher.html#request-events-checking-types)
	- example for checking app key in header, `src/EventListener/ApiRequestListener.php`:
	```php
	// src/EventListener/ApiRequestListener.php

	namespace App\EventListener;

	use Symfony\Component\HttpKernel\Event\RequestEvent;
	use Symfony\Component\HttpFoundation\JsonResponse;

	class ApiRequestListener
	{
		private $appKey;

		public function __construct(string $appKeyPath)
		{
			if (file_exists($appKeyPath)) {
				$this->appKey = file_get_contents($appKeyPath);
			} else {
				throw new \RuntimeException("API key file not found at: $appKeyPath");
			}
		}

		public function onKernelRequest(RequestEvent $event)
		{
			$request = $event->getRequest();
			// Only check API key for paths starting with /api and /auth
			if (strpos($request->getPathInfo(), '/api') === 0 || $request->getPathInfo() === '/auth') {
				if (!$request->headers->has('X-APP-KEY') || $request->headers->get('X-APP-KEY') !== $this->appKey) {
					$response = new JsonResponse(
						[
							'status' => 401,
							'message' => 'Invalid APP Key'
						], 
						JsonResponse::HTTP_UNAUTHORIZED
					);
					$event->setResponse($response); // Set the response and stop further event propagation
					$event->stopPropagation();
				}
			}
		}
	}
	```
#### EasyAdmin [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/index.html)
- Modifying the Layout(Dashboard, Menu Items, User Display, etc) : [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/dashboards.html)
- CRUD Controllers : [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/crud.html)
	- Custom Redirect : [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/crud.html#custom-redirect-after-creating-or-editing-entities)
- Design (Overriding Templates and ...): [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/design.html)
	- layout template: [Click Here](https://github.com/EasyCorp/EasyAdminBundle/blob/4.x/src/Resources/views/layout.html.twig)
	- Override Dashboard Title:
		```php
		# templates/adminDashboard.html.twig

		{% extends '@!EasyAdmin/page/content.html.twig' %}

		{% block page_title %}
			Uno Dashboard
		{% endblock %}

		{% block content_title %}
			{# LEAVE EMPTY IF YOU DONT WANT CONTENT TITLE #}
		{% endblock %}

		{% block main %}
			<h1> Admin Dashboard </h1>
			<div>{{text}}</div>
		{% endblock %}

		```
- Actions: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/actions.html)
	- Restricting Actions based on User Role: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/actions.html#restricting-actions)
- Fields: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/fields.html)
	- Field Types: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/fields.html#field-types)
- Events: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/events.html)
- Generate Admin Urls: [Click Here](https://symfony.com/bundles/EasyAdminBundle/current/actions.html#generating-urls-to-symfony-actions-integrated-in-easyadmin)
- EasyAdmin Crud Controller Overridable Methods:
	1. `createEntity()` -> Override the EasyAdmin Entity Creation Logic with custom logic
	2. `createEditForm()` -> Override the EasyAdmin Edit Form Creation
	3. `updateEntity()` -> Override the EasyAdmin Entity Update Logic with custom logic
#### ApiPlatform
- ApiPlatform x Lexik(JWT) : [Click Here](https://api-platform.com/docs/core/jwt/)
- Securing Api's : [Click Here](https://api-platform.com/docs/core/security/)
- Custom Voter to handling custom Logic for Item Operation(**NOT COLLECTION**): [Click Here](https://api-platform.com/docs/core/security/#hooking-custom-permission-checks-using-voters)
	- to create a voter using `Maker Bundle`: `php bin/console make:voter <VOTER-NAME>`
- Custom Permission Checks Using Extensions(**FOR COLLECTION**): [Click Here](https://api-platform.com/docs/core/extensions/#custom-doctrine-orm-extension)
#### Liip/Imagine-Bundle (*For image serving*)
- Basic Usage: [Click Here](https://symfony.com/bundles/LiipImagineBundle/current/basic-usage.html)
- Default Config: [Click Here](https://symfony.com/bundles/LiipImagineBundle/current/configuration.html)
- Custom Cache Resolvers: [Click Here](https://symfony.com/doc/current/LiipImagineBundle/cache-resolvers-custom.html)
#### Symfony.UX Dropzone (*For image Manipulation*)
- If any error with *UX Dropzone*, try to run the following:
	- `npm install --force`
	- `npm run dev`
- Docs: [Click Here](https://symfony.com/bundles/ux-dropzone/current/index.html)

#### Symfony UX [Click Here](https://symfony.com/bundles/StimulusBundle/current/index.html)
- Stimulus Docs: [Click Here](https://stimulus.hotwired.dev):
	- Controllers: [Click Here](https://stimulus.hotwired.dev/reference/controllers)
	- Actions: [Click Here](https://stimulus.hotwired.dev/reference/actions)
	- Outlets: [Click Here](https://stimulus.hotwired.dev/reference/outlets)
	```twig
	<!-- Example in media_preview.html.twig -->
		<div 
			class="dh_media_preview_wrapper rounded-4 is-loading"
			{{ stimulus_controller('dh-media-preview') }}
		>
			<img src="{{ thumbnailUrl }}" 
				{{ stimulus_target('dh-media-preview', 'mediaPreviewImg') }}
				{{ stimulus_action('dh-media-preview', 'onImageLoad', 'load')|stimulus_action('dh-media-preview', 'onImageError', 'error') }}
				loading="lazy" 
				class="dh_media_preview dh_media_preview_img" 
			/>
			<div
				class="d-none"
				{{ stimulus_target('dh-media-preview', 'errorMediaPreview') }}
			>
				<p>Error on Loading Preview</p>
			</div>
		</div>
	```
	```js
	// assets/controllers/dh-media-preview_controller.js
	import { Controller } from '@hotwired/stimulus';

	/* stimulusFetch: 'lazy' */
	export default class extends Controller {
		static targets = [ "mediaPreviewImg", "errorMediaPreview"];

		connect() {
			console.log("Connected to Stimulus Media Controller")
		}

		onImageLoad(event) {
			console.log('Image loaded:', event.target);
			// Your image load logic here
			this.element.classList.remove("is-loading");
		}

		onImageError(event){
			console.log("Image Loading Error", event.target);;

			this.element.classList.remove("is-loading");
			this.element.classList.add('is-error');

			this.errorMediaPreviewTarget.classList.remove("d-none");
		}
	}
	```
	*NOTE: controllers should be named as such `[name]_controller.js`
	*

- LazyImage: [Click Here](https://symfony.com/bundles/ux-lazy-image/current/index.html)

### FFMpeg For Video Manipulation:
- Docs (using fmonts wrapper for Symfony): [Click Here](https://github.com/fmonts/ffmpeg-bundle)
- Usage example in `src/Service/VideoService.php`:
```php
<?php

namespace App\Service;
use App\Entity\Media; // Adjust this to your Media entity's namespace
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vich\UploaderBundle\Storage\StorageInterface;
use FFMpeg\Coordinate\TimeCode;
use Symfony\Component\HttpFoundation\File\File;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;

class VideoService
{
	private StorageInterface $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getFramePictureAt(File $mediaFile, $useMiddleFrame = false, $framePosition = 1)
    {
        // Use FFmpeg or similar tools to extract a frame from the video
		$savePath = $mediaFile->getPath();
		$videoPath = $mediaFile->getRealPath();
		$frameFileName = 'frame-' . pathinfo($mediaFile->getFilename(), PATHINFO_FILENAME) . '.jpg'; // Assuming .jpg extension for the frame

		$ffmpeg = FFMpeg::create();
		// Open the video file
		$video = $ffmpeg->open($videoPath);

		// override frame position if we are using middle frame
		if ($useMiddleFrame){
			$framePosition = $this->getMiddleFrameOfVideo($videoPath);
		}

		try {
			// Extract a frame from the video at the specified position and save it
			$frame = $video->frame(TimeCode::fromSeconds($framePosition));
			$frame->save($savePath . '/' . $frameFileName);
		} catch (\Exception $e) {
			// Log the exception to help with debugging
			error_log('Error saving frame: ' . $e->getMessage());
			// Consider returning an error or null to indicate failure
			return null;
		}

		return [
			'frameFileName' => $frameFileName
		];
    }

	public function serveVideo(Media $media): Response
    {
        // Resolve the path to the video file using VichUploaderBundle
        $videoPath = $this->storage->resolvePath($media, 'file');
        
        // Check if the video file exists
        if (!$videoPath || !file_exists($videoPath)) {
            throw new NotFoundHttpException('The video file does not exist.');
        }

        // Create and return a BinaryFileResponse to serve the video file
        $response = new BinaryFileResponse($videoPath);
        $response->headers->set('Content-Type', 'video/mp4'); // Adjust the MIME type if necessary

        // Optional: Set additional response headers, e.g., for caching, filename, etc.
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE, // Use DISPOSITION_ATTACHMENT to force download
            $media->getFileName() ?? 'video.mp4'
        );

        return $response;
    }

	public function getMiddleFrameOfVideo($mediaFilePath)
	{
		$middleFramePosition = 1;
		$ffprobe = FFProbe::create();

		try {
			$mediaFileDuration = $ffprobe->format($mediaFilePath)->get('duration');
			$framePosition = $mediaFileDuration / 2;
		} catch (\Throwable $th) {
			//throw $th;

			error_log('Error saving frame: ' . $th->getMessage());
		}

		return $middleFramePosition;
	}

	public function getVideoUrl(Media $media): ?string
	{
		$videoUrl = $this->storage->resolveUri($media, 'file');

		return $videoUrl;
	}
}
```


#### Vich Uploader Bundle (*Handling Image Uploads*)
- Docs: [Click Here](https://github.com/dustin10/VichUploaderBundle/blob/master/docs/index.md)
- Usage: [Click Here](https://github.com/dustin10/VichUploaderBundle/blob/master/docs/usage.md)
- File Namers: [Click Here](https://github.com/dustin10/VichUploaderBundle/blob/master/docs/namers.md)

#### Bootstrap Icons Using Twig:
- Docs: [Click Here](https://github.com/whatwedo/TwigBootstrapIcons)
- Bootstrap Icons: [Click Here](https://icons.getbootstrap.com)


#### Font Awesome Icons: [Click Here](https://fontawesome.com/icons)

### Tips ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)

#### EasyAdmin (4.8.0)
- To get instance of entity in the Crud Controller: `$entity = $this->getContext()->getEntity()->getInstance();`
- Add Flash Messages to CRUD Event:
	- `$this->addFlash('flash_color', 'Flash Message')`
	- `flash_color` is similar to bootstrap color, examples: `success`, `danger`, `info` 

#### ApiPlatform ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
- When using an Api/WebService, use the `Accept` header to get in the `Media Type` you want (e.g, `application/json`)
- For errors such as `Serialization of 'Symfony\Component\HttpFoundation\File\File' is not allowed`:
	- Just use `__serialize` on that entity, and define the attributes you want to be serialized

### Miscellaneous ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg)
- Clear git cache after folder/file was already commited, then added to `.gitignore`: [Click Here](https://stackoverflow.com/questions/6535362/gitignore-after-commit)
- Change version tag: `git tag -a v1.0.0 -m "Release version 1.0.0"`
- `git commit -m "Fixed Issue #<issue number here>"` will link an the issue to the commit 
- `git commit -m "Fixed Issue fix/closes/resolve #<issue number here>"` will link an the issue to the commit and closes it upon merging
- CSS:
	- if you want `animation` last key frame to stick as element property, use `animation-fill-mode: forwards;`

## Deployment Notes ![Custom Badge](https://img.shields.io/badge/IMPORTANT-red.svg)
- On __PRODUCTION SERVER__, you need to make sure that your user can collaborate with the web server without permission issues:
```bash
sudo usermod -aG www-data your_username
```
*Replace `your_username` with your actual username.*
*Log out and log back into your session for the group changes to take effect.*

- Set the `www-data` group to have write permissions and use the SGID bit for inheritance:

```bash
sudo chown -R your_username:www-data /var/www
sudo chmod -R 775 /var/www
sudo find /var/www -type d -exec chmod g+s {} \;
```

- Ensure the web server can write to the necessary directories:

```bash
sudo chown -R your_username:www-data /var/www/project_uno_admin
sudo find /var/www/bravo6_admin -type d -exec chmod 775 {} \;
sudo find /var/www/bravo6_admin -type f -exec chmod 664 {} \;
sudo chmod -R g+w /var/www/bravo6_admin/var
sudo chmod -R g+w /var/www/bravo6_admin/public/bundles
```
- If using git for that specific repo, when pulling, you might want to check the permissions on each `git pull` to make sure
that permissions are set correctly, optionally you could create a bash script with the following, that can be called on each
`git pull`:

```bash
#!/bin/bash
sudo chown -R your_username:www-data /var/www/project_uno_admin
sudo find /var/www/bravo6_admin -type d -exec chmod 775 {} \;
sudo find /var/www/bravo6_admin -type f -exec chmod 664 {} \;
sudo chmod -R g+w /var/www/bravo6_admin/var
sudo chmod -R g+w /var/www/bravo6_admin/public/bundles
```
*Run this script whenever needed to ensure permissions are correct.*

### Generating SSL Certificate with Let's Encrypt:
- Sometimes the `ssl nginx config` is created in the `default` configuration of nginx, so on generating new `SSL Certificate` for a domain/website
make sure config is in the correct file.


# Configuration for Mercure Hub ![Custom Badge](https://img.shields.io/badge/NEW-blue.svg) ![Custom Badge](https://img.shields.io/badge/IMPORTANT-red.svg)

## Installation
1. Download archieve for corresponding OS(*if the legacy version is not necessary, do not use that one*): [Click Here](https://github.com/dunglas/mercure/releases)
2. Extract the contents of the archieve in a directory and then run the following commands using a Terminal/Powershell from that directory.

## Generating RSA Key Pair
### Generate the Private Key

```bash
ssh-keygen -t rsa -b 4096 -m PEM -f <YOUR-PUBLISHER-KEY-NAME>.key
ssh-keygen -t rsa -b 4096 -m PEM -f <YOUR-SUBSCRIBER-KEY-NAME>.key
```
*Do not add a passphrase to ensure automated systems can use the key without manual intervention.*

### Extract the Public Key (*if not auto-generated*)
```bash
openssl rsa -in <YOUR-PUBLISHER-KEY-NAME>.key -pubout -outform PEM -out <YOUR-PUBLISHER-KEY-NAME>.key.pub
openssl rsa -in <YOUR-SUBSCRIBER-KEY-NAME>.key -pubout -outform PEM -out <YOUR-SUBSCRIBER-KEY-NAME>.key.pub
```
*This creates jwtRS256.key.pub, the public key, to be configured in the Mercure Hub.*

## Configuring the Mercure Hub
### Set Environment Variables
For Unix-based systems (Linux/macOS):
```bash
MERCURE_PUBLISHER_JWT_KEY=$(cat <YOUR-PUBLISHER-KEY-NAME>.key.pub) \
MERCURE_SUBSCRIBER_JWT_KEY=$(cat <YOUR-SUBSCRIBER-KEY-NAME>.key.pub) \
```
For Windows (in PowerShell):
```bash
$env:MERCURE_PUBLISHER_JWT_KEY=(Get-Content <YOUR-PUBLISHER-KEY-NAME>.key.pub); $env:MERCURE_SUBSCRIBER_JWT_KEY=(Get-Content <YOUR-SUBSCRIBER-KEY-NAME>.key.pub);
```
*Ensure the path to `<YOUR-PUBLISHER-KEY-NAME>.key.pub` and `<YOUR-SUBSCRIBER-KEY-NAME>.key.pub` are correct.*

### Configure the Caddyfile
In your Caddyfile or Caddyfile.dev, use RSA for the JWT algorithm:
```Caddyfile
mercure {
  publisher_jwt {env.MERCURE_PUBLISHER_JWT_KEY} RS256
  subscriber_jwt {env.MERCURE_SUBSCRIBER_JWT_KEY} RS256
  # other configurations...
}
```
*This tells the Mercure Hub to use RSA and RS256 to verify JWTs.(the __RS256__ parts)*

Modify the port number(*default is 80, 443*):
```Caddyfile
{$SERVER_NAME:localhost:3579} {
  ...
}
```

## Run Mercure Hub

### Windows
For Development (*Using `--config Caddyfile.dev`*)
```bash
$env:MERCURE_PUBLISHER_JWT_KEY=(Get-Content <YOUR-PUBLISHER-KEY-NAME>.key.pub); $env:MERCURE_SUBSCRIBER_JWT_KEY=(Get-Content <YOUR-SUBSCRIBER-KEY-NAME>.key.pub); .\mercure.exe run --config Caddyfile.dev
```
For Production (*Using `--config Caddyfile`*)
```bash
$env:MERCURE_PUBLISHER_JWT_KEY=(Get-Content <YOUR-PUBLISHER-KEY-NAME>.key.pub); $env:MERCURE_SUBSCRIBER_JWT_KEY=(Get-Content <YOUR-SUBSCRIBER-KEY-NAME>.key.pub); .\mercure.exe run --config Caddyfile
```

### Linux/MacOs
For Development (*Using `--config Caddyfile.dev`*)
```bash
MERCURE_PUBLISHER_JWT_KEY=$(cat <YOUR-PUBLISHER-KEY-NAME>.key.pub) \
MERCURE_SUBSCRIBER_JWT_KEY=$(cat <YOUR-SUBSCRIBER-KEY-NAME>.key.pub) \
./mercure run --config Caddyfile.dev
```
For Production (*Using `--config Caddyfile`*)
```bash
MERCURE_PUBLISHER_JWT_KEY=$(cat <YOUR-PUBLISHER-KEY-NAME>.key.pub) \
MERCURE_SUBSCRIBER_JWT_KEY=$(cat <YOUR-SUBSCRIBER-KEY-NAME>.key.pub) \
./mercure run --config Caddyfile
```
The server is now available on https://localhost (TLS is automatically enabled, learn how to disable it). In development mode, anonymous subscribers are allowed and the debug UI is available on https://localhost/.well-known/mercure/ui/.

*__Note__*: if you get an error similar to bind: address already in use, it means that the port 80 or 443 is already used by another service (the usual suspects are Apache and NGINX). Before starting Mercure, stop the service using the port(s) first, or set the SERVER_NAME environment variable to use a free port (ex: SERVER_NAME=:3000).

### Security Considerations

* __Keep the Private Key Secure__: The private key (jwtRS256.key) should be securely stored and not exposed.
* __Environment Variables__: Use a secure method to set environment variables, especially in cloud environments or CI/CD pipelines.
* __Permissions__: Set appropriate file permissions for the key files to restrict access.

### Mercure Docs:
- __Install__: [Click Here](https://mercure.rocks/docs/hub/install)
- __Config__: [Click Here](https://mercure.rocks/docs/hub/config)
- __Using NGINX As Proxy__: [Click Here](https://mercure.rocks/docs/hub/config)
