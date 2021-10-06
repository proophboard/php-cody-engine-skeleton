<?php

declare(strict_types=1);

namespace Acme\System\Api;

use EventEngine\EventEngine;
use EventEngine\EventEngineDescription;

final class EventEngineConfig implements EventEngineDescription
{
    public static function describe(EventEngine $eventEngine): void
    {
        $eventEngine->disableAutoProjecting();
        $eventEngine->enableMetadataForwarding();
    }
}
