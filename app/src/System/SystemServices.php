<?php

declare(strict_types=1);

namespace Acme\System;

use Acme\Domain\Api\Aggregate;
use Acme\System\Api\EventEngineConfig;
use Acme\System\Api\SystemQuery;
use Acme\System\Api\SystemType;
use EventEngine\Data\ImmutableRecordDataConverter;
use EventEngine\JsonSchema\OpisJsonSchema;
use EventEngine\Logger\LogEngine;
use EventEngine\Logger\SimpleMessageEngine;
use EventEngine\Runtime\Flavour;
use EventEngine\Runtime\PrototypingFlavour;
use EventEngine\Schema\Schema;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

trait SystemServices
{
    public function systemDescriptions(): array
    {
        return [
            SystemType::class,
            SystemQuery::class,
            EventEngineConfig::class,
        ];
    }

    public function schema(): Schema
    {
        return $this->makeSingleton(Schema::class, function () {
            return new OpisJsonSchema();
        });
    }

    public function flavour(): Flavour
    {
        return $this->makeSingleton(Flavour::class, function () {
            return new PrototypingFlavour(new ImmutableRecordDataConverter(
                Aggregate::CLASS_MAP
            ));
        });
    }

    public function healthCheckResolver(): HealthCheckResolver
    {
        return $this->makeSingleton(HealthCheckResolver::class, function () {
            return new HealthCheckResolver();
        });
    }

    public function logger(): LoggerInterface
    {
        return $this->makeSingleton(LoggerInterface::class, function () {
            $streamHandler = new StreamHandler('php://stderr');

            return new Logger('EventEngine', [$streamHandler]);
        });
    }

    public function logEngine(): LogEngine
    {
        return new SimpleMessageEngine($this->logger());
    }
}
