<?php

declare(strict_types=1);

use Acme\Persistence\WriteModelStreamProjection;
use EventEngine\EventEngine;
use Prooph\EventStore\Projection\ProjectionManager;
use Psr\Container\ContainerInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require 'config/container.php';

/** @var EventEngine $eventEngine */
$eventEngine = $container->get(EventEngine::class);

$eventEngine->bootstrap(getenv('PROOPH_ENV') ?: 'prod', true);

/** @var ProjectionManager $projectionManager */
$projectionManager = $container->get(ProjectionManager::class);

echo "Resetting " . WriteModelStreamProjection::NAME . "\n";

$projectionManager->resetProjection(WriteModelStreamProjection::NAME);
