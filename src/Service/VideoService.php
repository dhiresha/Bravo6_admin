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