<?php

declare(strict_types=1);

namespace Acme\System;

use EventEngine\Messaging\Message;
use EventEngine\Querying\Resolver;

final class HealthCheckResolver implements Resolver
{
    public function resolve(Message $query): array
    {
        return [
            'system' => true,
        ];
    }
}
