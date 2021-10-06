<?php
declare(strict_types = 1);

use EventEngine\EventEngine;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Stratigility\Middleware\OriginalMessages;
use Laminas\Stratigility\MiddlewarePipe;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\ProblemDetails\ProblemDetailsMiddleware;
use Mezzio\ProblemDetails\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use function Laminas\Stratigility\middleware;
use function Laminas\Stratigility\path;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

register_shutdown_function(static function() {
    $lastError = error_get_last();

    if (is_array($lastError) && isset($lastError['type']) && $lastError['type'] === E_ERROR) {
        /** @var \Psr\Container\ContainerInterface $container */
        $container = require 'config/container.php';

        /* @var $logger \Psr\Log\LoggerInterface */
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        $logger->critical($lastError['message'] . ' ' . var_export($lastError, true));

        $serverRequest = Laminas\Diactoros\ServerRequestFactory::fromGlobals();

        $emitter = new Laminas\HttpHandlerRunner\Emitter\SapiEmitter();
        $response = $container->get(ProblemDetailsResponseFactory::class);

        $emitter->emit(
            $response->createResponse(
                $serverRequest,
                Fig\Http\Message\StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                'An error has occurred.',
            )
        );
    }
});


/**
 * Self-called anonymous function that creates its own scope and keep the global namespace clean.
 */
(static function () {
    try {
        /** @var \Psr\Container\ContainerInterface $container */
        $container = require 'config/container.php';

        //Note: this is important and needs to happen before further dependencies are pulled
        $env = getenv('PROOPH_ENV')?: 'prod';
        $devMode = $env === EventEngine::ENV_DEV;

        $app = new MiddlewarePipe();

        $app->pipe($container->get(ProblemDetailsMiddleware::class));

        $app->pipe(new BodyParamsMiddleware());

        $app->pipe(new OriginalMessages());

        $app->pipe(path(
            '/api',
            middleware(function (Request $req, RequestHandler $handler) use($container, $env, $devMode): Response {
                /** @var FastRoute\Dispatcher $router */
                $router = require 'config/api_router.php';

                $route = $router->dispatch($req->getMethod(), $req->getUri()->getPath());

                if ($route[0] === FastRoute\Dispatcher::NOT_FOUND) {
                    return new EmptyResponse(404);
                }

                if ($route[0] === FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
                    return new EmptyResponse(405);
                }

                foreach ($route[2] as $name => $value) {
                    $req = $req->withAttribute($name, $value);
                }

                if(!$container->has($route[1])) {
                    throw new \RuntimeException("Http handler not found. Got " . $route[1]);
                }

                $container->get(EventEngine::class)->bootstrap($env, $devMode);

                /** @var RequestHandler $httpHandler */
                $httpHandler = $container->get($route[1]);

                return $httpHandler->handle($req);
            })
        ));

        $server = new RequestHandlerRunner(
            $app,
            new SapiEmitter(),
            [ServerRequestFactory::class, 'fromGlobals'],
            function (Throwable $e) {
                $generator = new ErrorResponseGenerator();
                return $generator($e, new ServerRequest(), new \Laminas\Diactoros\Response());
            }
        );

        $server->run();
    } catch (\Throwable $e) {
        /** @var \Psr\Container\ContainerInterface $container */
        $container = require 'config/container.php';

        /* @var $logger \Psr\Log\LoggerInterface */
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        $logger->critical('exception: ' . $e->getMessage());
        $logger->critical((string) $e);

        $serverRequest = Laminas\Diactoros\ServerRequestFactory::fromGlobals();

        $emitter = new Laminas\HttpHandlerRunner\Emitter\SapiEmitter();
        $response = $container->get(ProblemDetailsResponseFactory::class);

        $emitter->emit(
            $response->createResponseFromThrowable(
                $serverRequest,
                $e
            )
        );
    }
})();
