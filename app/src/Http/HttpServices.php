<?php

declare(strict_types=1);

namespace Acme\Http;

use Acme\Http\Handler\MessageBoxHandler;
use Acme\Http\Handler\MessageSchemaHandler;
use Acme\System\PsrErrorLogger;
use EventEngine\EeCockpit\EeCockpitHandler;
use Laminas\Diactoros\Response;
use Mezzio\ProblemDetails\ProblemDetailsMiddleware;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;

trait HttpServices
{
    public function httpMessageBox(): MessageBoxHandler
    {
        return $this->makeSingleton(MessageBoxHandler::class, function () {
            return new MessageBoxHandler($this->eventEngine(), $this->logger());
        });
    }

    public function eventEngineHttpMessageSchema(): MessageSchemaHandler
    {
        return $this->makeSingleton(MessageSchemaHandler::class, function () {
            return new MessageSchemaHandler($this->eventEngine());
        });
    }

    public function problemDetailsMiddleware(): ProblemDetailsMiddleware
    {
        return $this->makeSingleton(ProblemDetailsMiddleware::class, function () {
            $errorHandler = new ProblemDetailsMiddleware($this->problemDetailsResponseFactory());
            $errorHandler->attachListener(new PsrErrorLogger($this->logger()));

            return $errorHandler;
        });
    }

    public function problemDetailsResponseFactory(): ProblemDetailsResponseFactory
    {
        return $this->makeSingleton(ProblemDetailsResponseFactory::class, function () {
            $isDevEnvironment = $this->config->stringValue('environment', 'prod') === 'dev';

            return new ProblemDetailsResponseFactory(
                function () {
                    return new Response();
                },
                $isDevEnvironment
            );
        });
    }

    public function eeCockpitHandler(): EeCockpitHandler
    {
        return $this->makeSingleton(EeCockpitHandler::class, function () {
            return new EeCockpitHandler($this->eventEngine(), $this->documentStore());
        });
    }
}
