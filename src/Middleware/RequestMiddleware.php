<?php

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Kafkiansky\SymfonyMiddleware\Psr\PsrResponseTransformer;

final class RequestMiddleware implements MiddlewareInterface
{
    const URL_NOT_FOUND = 'geometry_calc.url_404';
    const IP_REQUESTER = 'geometry_calc.ip_requester';

    private ParameterBagInterface $parameterBag;
    private PsrResponseTransformer $psrResponseTransformer;
    private LoggerInterface $logger;
    private array $controlHeaders;

    /**
     * Constructor for the RequestMiddleware class
     *
     * @param ParameterBagInterface $parameterBag
     * @param PsrResponseTransformer $psrResponseTransformer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ParameterBagInterface  $parameterBag,
        PsrResponseTransformer $psrResponseTransformer,
        LoggerInterface        $logger
    )
    {
        $this->parameterBag = $parameterBag;
        $this->psrResponseTransformer = $psrResponseTransformer;
        $this->logger = $logger;

        // Control headers could be stored in a database.
        // You could use the user-agent header when you know
        // your API will accept requests only from other APIs,
        // e.g. for a stock integration service.
        $this->controlHeaders = [
            'not_allowed' => [
                'range' => '*',
                'te' => '*',
                'expect' => '*',
                'referer' => '*',
            ],
            'allowed' => [
                'accept-encoding' => '*',
                'content-length' => '*',
                'x-php-ob-level' => '*',
                'pragma' => '*',
                'host' => '*',
                'cache-control' => '*',
            ],
            'must_be' => [
                'connection' => 'close',
                'upgrade-insecure-requests' => 1,
                'user-agent' => 'magento.my-module;shopify.my-app',
                'accept' => 'application/json',
            ],
        ];
    }

    /**
     * Check if headers are valid
     *
     * @param ServerRequestInterface $request
     * @return bool|array
     */
    private function validHeaders(ServerRequestInterface $request): bool|array
    {
        $headers = $request->getHeaders();
        $mustBeHeaders = $this->controlHeaders['must_be'];
        $allowedHeaders = $this->controlHeaders['allowed'];
        $notAllowedHeaders = $this->controlHeaders['not_allowed'];

        foreach ($headers as &$value) {
            $value = $value[0];
        }

        ksort($headers);
        ksort($mustBeHeaders);

        // Here I'm using array_diff_key instead of loop(s)
        // in order to avoid exponential complexity
        $mustBeKeyDiffFwd = array_diff_key($headers, $mustBeHeaders);
        $missing = array_diff_key($mustBeHeaders, $headers);
        $notAllowedMustBe = array_diff_key($mustBeKeyDiffFwd, $allowedHeaders);
        $invalid = array_diff($headers, $mustBeHeaders);
        $notAllowedInvalid = array_diff_key($invalid, $allowedHeaders);
        $errors = [];

        if ($notAllowedHeaders !== array_diff_key($notAllowedHeaders, $headers)) {
            $errors['notAllowed'] = array_keys($notAllowedMustBe);
        }

        if (!empty($notAllowedMustBe)) {
            $errors['notAllowed'] = $errors['notAllowed'] ?? [];
            $errors['notAllowed'][] = array_keys($notAllowedMustBe);
        }

        if (!empty($missing)) {
            $errors['missing'] = array_keys($missing);
        }

        if (!empty($notAllowedInvalid)) {
            $errors['invalid'] = array_keys($invalid);
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestMethod = $request->getMethod();
        $notFoundUrl = $this->parameterBag->get(self::URL_NOT_FOUND);
        $ipRequester = $this->parameterBag->get(self::IP_REQUESTER);
        $validHeaders = $this->validHeaders($request);
        $logMessage = '';

        // 1. Headers
        if (true !== $validHeaders) {
            $logMessage = "Invalid Headers\n";
        }

        // 2. Method
        if ('POST' !== $requestMethod) {
            $logMessage = "Method $requestMethod is not allowed\n";
        }

        // 3. Requester
        if ($ipRequester !== $request->getServerParams()['REMOTE_ADDR']) {
            $logMessage = "Remote address is not allowed\n";
        }

        if (!empty($logMessage)) {
            // 4. Log errors
            $this->logger->notice($logMessage);

            // 5. Response
            $response = new RedirectResponse($notFoundUrl);
            return $this->psrResponseTransformer->toPsrResponse($response);
        }

        return $handler->handle($request);
    }
}
