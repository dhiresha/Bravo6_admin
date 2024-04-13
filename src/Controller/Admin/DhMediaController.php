<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Manager\MediaManager;
use App\Service\ImageService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\VideoService;

class DhMediaController extends AbstractController
{
	private VideoService $videoService;
	private ImageService $imageService;
	private MediaManager $mediaManager;

	public function __construct(
		VideoService $videoService,
		ImageService $imageService,
		MediaManager $mediaManager
	)
	{
		$this->videoService = $videoService;
		$this->imageService = $imageService;
		$this->mediaManager = $mediaManager;
	}

	#[Route('/uno_uploads/cache/{filter}/{path}', name: 'serve_cached_image', requirements: ['path' => '.+'])]
    public function serveCachedImage(string $path, string $filter): Response
    {
        // Construct the file path based on your UnoResolver cache structure
        $filePath = $this->getParameter('kernel.project_dir') . "/uno_uploads/cache/{$filter}/{$path}";

        // Ensure the file exists to prevent exposing internal paths or throwing errors
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('The image does not exist.');
        }

        // Return the binary file response
        return new BinaryFileResponse($filePath);
    }

	#[Route('/uno_media/uno_drive/{filename}', name: 'serve_media', requirements: ['filename' => '.+'])]
	public function serveMedia(string $filename): Response
    {
        // Find your Media entity based on the filename or other criteria
        $media = $this->mediaManager->getRepository()->findOneBy(['fileName' => $filename]);

        if (!$media) {
            throw new NotFoundHttpException('The media file does not exist.');
        }

        if (str_starts_with($media->getFileType(), 'video/')) {
            // Serve video file
            return $this->videoService->serveVideo($media);
        } elseif (str_starts_with($media->getFileType(), 'image/')) {
            // Serve image file, potentially using a similar approach as serveVideo or directly through VichUploaderBundle
            return $this->imageService->getImageUrl($media->getFileName()); // Implement serveImage accordingly
        } else {
            throw new NotFoundHttpException('Unsupported media type.');
        }
    }
}