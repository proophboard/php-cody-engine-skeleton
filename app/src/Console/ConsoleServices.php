<?php

declare(strict_types=1);

namespace Acme\Console;

use Acme\Console\Command\PrepareDatabase;

trait ConsoleServices
{
    public function prepareDatabase(): PrepareDatabase
    {
        return $this->makeSingleton(PrepareDatabase::class, function () {
            return new PrepareDatabase($this->multiModelStore(), $this->eventEngine());
        });
    }

    public static function consoleCommands(): array
    {
        return [
            PrepareDatabase::class,
        ];
    }
}
