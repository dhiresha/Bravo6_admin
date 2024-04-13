<?php

namespace App\Service;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

class UnoResolver implements ResolverInterface
{
    private $filesystem;
    private $router;
    private $cacheRoot;
	private $cachePrefix;

    public function __construct(Filesystem $filesystem, RouterInterface $router, string $cacheRoot, string $cachePrefix)
    {
        $this->filesystem = $filesystem;
        $this->router = $router;
        // Assuming 'uno_uploads' is under your project directory; adjust as needed.
        $this->cacheRoot = rtrim($cacheRoot, '/');
		$this->cachePrefix = $cachePrefix;
    }

    public function isStored($path, $filter): bool
    {
        // Implement logic to check if the image is stored
		$cachePath = $this->getCachePath($path, $filter);
		return $this->filesystem->exists($cachePath);
    }

    public function resolve($path, $filter): string
    {
		// Assuming there's a route named 'serve_cached_image' that serves the cached images
		return $this->router->generate('serve_cached_image', ['path' => $path, 'filter' => $filter], RouterInterface::ABSOLUTE_URL);
    }

    public function store(BinaryInterface $binary, $path, $filter): void
    {
        // Implement logic to store the processed image
		$cachePath = $this->getCachePath($path, $filter);
		$this->filesystem->mkdir(dirname($cachePath));
		file_put_contents($cachePath, $binary->getContent());
    }

    public function remove(array $paths, array $filters): void
    {
        // Implement logic to remove the cached images
		foreach ($filters as $filter) {
			foreach ($paths as $path) {
				$cachePath = $this->getCachePath($path, $filter);
				if ($this->filesystem->exists($cachePath)) {
					$this->filesystem->remove($cachePath);
				}
			}
		}
    }

	private function getCachePath($path, $filter): string
	{
		return sprintf('%s/%s/%s/%s', $this->cacheRoot, $this->cachePrefix, $filter, ltrim($path, '/'));
	}
}