<?php

namespace App\Service;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class ImageService
{
	private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function getImageUrl(string $imageName, string $filter = 'original'): ?string
    {
        return $this->cacheManager->getBrowserPath($imageName, $filter);
    }

    public function getThumbnailUrl(string $imageName, string $filter = 'thumbnail'): ?string
    {
        return $this->cacheManager->getBrowserPath($imageName, $filter);
    }

	public function applyFilter($image, $filter)
    {
        // Use LiipImagineBundle to apply filters to the image
        return $this->cacheManager->getBrowserPath($image, $filter);
    }
}