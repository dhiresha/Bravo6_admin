<?php

namespace App\Serializer;

use App\Entity\Media;
use App\Service\ImageService;
use App\Manager\MediaManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizer implements NormalizerInterface
{
    private $imageService;
	private MediaManager $mediaManager;

    public function __construct(
		ImageService $imageService,
		MediaManager $mediaManager,
		#[Autowire(service: 'serializer.normalizer.object')]
        private readonly NormalizerInterface $normalizer,
        private UrlGeneratorInterface $router,
	)
    {
        $this->imageService = $imageService;
		$this->mediaManager = $mediaManager;
    }

    public function normalize($media, string|null $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($media, $format, $context);	

        // Assuming you have a method in your ImageService to generate the URL
        // For example, using a specific filter set in LiipImagineBundle
        if ($media instanceof Media) {
            $data['media_preview_thumbnail'] = $this->imageService->getImageUrl($media->getFileName(), 'thumbnail');
            $data['media_original'] = $this->mediaManager->serveMedia($media);
        }

        return $data;
    }

    public function supportsNormalization($data, string|null $format = null, array $context = []): bool
    {
        return $data instanceof Media;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Media::class => true,
        ];
    }
}