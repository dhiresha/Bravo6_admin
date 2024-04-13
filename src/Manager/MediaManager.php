<?php

namespace App\Manager;

use App\Helper\MediaHelper;
use App\Entity\Media;
use App\Repository\MediaRepository;

class MediaManager
{
	private $mediaHelper;
	private MediaRepository $mediaRepository;

    public function __construct(MediaHelper $mediaHelper, MediaRepository $mediaRepository)
    {
        $this->mediaHelper = $mediaHelper;
        $this->mediaRepository = $mediaRepository;
    }

	public function getMediaHelper()
	{
		return $this->mediaHelper;
	}

	public function getRepository()
	{
		return $this->mediaRepository;
	}

	public function processMedia(Media $media, $filter = 'thumbnail')
    {
        return $this->getMediaHelper()->processMediaFile($media, $filter);
    }

	public function serveMedia(Media $media)
	{
		return $this->getMediaHelper()->serveMedia($media);
	}
}