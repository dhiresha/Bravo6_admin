<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use App\Entity\Media;
use Twig\TwigFunction;
use App\Manager\MediaManager;

class AppExtension extends AbstractExtension
{
	private MediaManager $mediaManager;

	public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

	public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', [$this, 'getMediaUrl']),
            new TwigFunction('getMediaThumbnail', [$this, 'getMediaThumbnail']),
        ];
    }

	public function getMediaUrl(Media $media): string
    {
		$mediaUrl = '';

		if (!empty($this->mediaManager->serveMedia($media))){
			$mediaUrl = $this->mediaManager->serveMedia($media);
		}

        return $mediaUrl;
    }

	public function getMediaThumbnail (Media $media, $pageName = 'index'): string
	{
		$mediaThumbnail = '';

		if ($pageName == 'index'){
			$mediaThumbnail = $this->mediaManager->getMediaHelper()->applyFilter($media, 'thumbnail_large');
		} else {
			$mediaThumbnail = $this->mediaManager->getMediaHelper()->applyFilter($media, 'original');
		}

		return $mediaThumbnail;
	}
}