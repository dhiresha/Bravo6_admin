<?php

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
        // if (strpos($request->getPathInfo(), '/api') === 0 || $request->getPathInfo() === '/auth') {
        //     if (!$request->headers->has('X-APP-KEY') || $request->headers->get('X-APP-KEY') !== $this->appKey) {
		// 		$response = new JsonResponse(
		// 			[
		// 				'status' => 401,
		// 				'message' => 'Invalid APP Key'
		// 			], 
		// 			JsonResponse::HTTP_UNAUTHORIZED
		// 		);
        //         $event->setResponse($response); // Set the response and stop further event propagation
        //         $event->stopPropagation();
        //     }
        // }
    }
}
