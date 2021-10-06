<?php

declare(strict_types=1);

namespace Acme\Domain;

use Acme\Domain\Api\Aggregate;
use Acme\Domain\Api\Command;
use Acme\Domain\Api\Event;
use Acme\Domain\Api\Listener;
use Acme\Domain\Api\Projection;
use Acme\Domain\Api\Query;
use Acme\Domain\Api\Type;

trait DomainServices
{
    public function domainDescriptions(): array
    {
        return [
            Type::class,
            Command::class,
            Event::class,
            Query::class,
            Aggregate::class,
            Projection::class,
            Listener::class,
        ];
    }
}
