<?php

namespace App\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\StandardOutput;

class BaseController extends AbstractController
{
    protected LoggerInterface $logger;

    /**
     * Constructor for the BaseController class
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle an exception
     *
     * @param Exception $exception
     * @param StandardOutput $output
     */
    protected function catchException(Exception $exception, StandardOutput &$output): void
    {
        // 1. Log exception
        $this->logger->critical(
            $exception->getMessage(),
            json_decode(json_encode($exception), true)
        );

        // 2. Output standard error
        $output->status = 500;
        $output->errors[] = [
            'code' => $exception->getCode(),
        ];

        // TODO: 3. Notify tech support
    }

    /**
     * Respond to a HTTP request
     *
     * @param StandardOutput $output
     * @param array $headers
     * @return JsonResponse
     */
    protected function respond(StandardOutput $output, array $headers = []): JsonResponse
    {
        $response = new JsonResponse($output->asArray(), $output->status);
        $response->headers->remove('server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
