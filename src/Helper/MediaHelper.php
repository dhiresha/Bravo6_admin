<?php

namespace App\Helper;

use App\Entity\Media;
use App\Service\ImageService;
use App\Service\VideoService;

class MediaHelper
{
	private $videoService;
    private $imageService;

    public function __construct(VideoService $videoService, ImageService $imageService)
    {
        $this->videoService = $videoService;
        $this->imageService = $imageService;
    }

	public function processMediaFile(Media $media, $filter = '')
    {
		$processData = [];
        if (str_starts_with($media->getFileType(), 'video/')) {
            // Extract a frame from the video
            $processData = $this->videoService->getFramePictureAt($media->getFile(), true); // Example frame position
			$processData['process_type'] = 'video';
		} else if (str_starts_with($media->getFileType(), 'image/')) {
			$processData['process_type'] = 'image';
		}

        return $processData;
    }

	public function serveMedia(Media $media){
		if (str_starts_with($media->getFileType(), 'video/')) {            
            // Optionally, apply a filter using LiipImagine or similar service
            return $this->videoService->getVideoUrl($media);
        } elseif (str_starts_with($media->getFileType(), 'image/')) {
            // Directly apply a filter to the image
            return $this->imageService->getImageUrl($media->getFileName());
        }
	}

	public function applyFilter(Media $media, $filter = 'thumbnail'){
		$processType = $media->getAdditionalData()['process_type'] ?? null;
		$imageToApplyFilter = null;

		if (!empty($processType)){
			switch ($processType) {
				case 'video':
					$imageToApplyFilter = $media->getAdditionalData()['frameFileName'] ?? null;
					break;
				case 'image':
					$imageToApplyFilter = $media->getFileName();
					break;
				default:
					# code...
					break;
			}
		}

		return $this->imageService->applyFilter($imageToApplyFilter, $filter);
	}
}