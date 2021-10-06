<?php

declare(strict_types=1);

namespace Acme\System;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function json_encode;
use function uniqid;

final class PsrErrorLogger
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Acts as a Zend\Stratigility\Middleware\ErrorHandler::attachListener() listener
     */
    public function __invoke(Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        $id = uniqid('request_');
        $this->logger->info('Request (' . $id . '): [' . $request->getMethod() . '] ' . $request->getUri());
        $this->logger->info('Request-Headers (' . $id . '): ' . json_encode($request->getHeaders()));
        $this->logger->info('Request-Body (' . $id . '): ' . $request->getBody());
        $this->logger->error('Error (' . $id . '): ' . $error);
    }
}
