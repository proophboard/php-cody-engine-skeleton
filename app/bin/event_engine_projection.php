<?php

declare(strict_types=1);

use Acme\Persistence\WriteModelStreamProjection;
use EventEngine\EventEngine;
use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

/** @var EventEngine $eventEngine */
$eventEngine = $container->get(EventEngine::class);

/** @var WriteModelStreamProjection $writeModelStreamProjection */
$writeModelStreamProjection = $container->get(WriteModelStreamProjection::class);

$env = getenv('PROOPH_ENV') ?: 'prod';

$eventEngine->bootstrap($env, true);

$devMode = $env === EventEngine::ENV_DEV;

if ($devMode) {
    $iterations = 0;

    while (true) {
        $writeModelStreamProjection->run(false);
        $iterations++;

        if ($iterations > 100) {
            //force reload in dev mode by exiting with error so docker restarts the container
            exit(1);
        }

        usleep(100);
    }
} else {
    $writeModelStreamProjection->run();
}
