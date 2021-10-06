<?php

declare(strict_types=1);

namespace Acme\Domain\Api;

use EventEngine\EventEngine;
use EventEngine\EventEngineDescription;

class Type implements EventEngineDescription
{
    /**
     * Define constants for query return types. Do not mix up return types with Acme\Domain\Api\Aggregate types.
     * Both can have the same name and probably represent the same data but you can and should keep them separated.
     * Aggregate types are for your write model and query return types are for your read model.
     *
     * @see \Acme\Domain\Api\Query for more about query return types
     *
     * @example
     *
     * const USER = 'User';
     *
     * You can use private static methods to define the type schemas and then register them in event engine together with the type name
     * private static function user(): ObjectType
     * {
     *      return JsonSchema::object([
     *          Payload::USER_ID => Schema::userId(),
     *          Payload::USERNAME => Schema::username()
     *      ]);
     * }
     *
     * Queries should only use type references as return types (at least when return type is an object).
     */

    public static function describe(EventEngine $eventEngine): void
    {
        /**
         * Register all types returned by queries
         *
         * @see \Acme\Domain\Api\Query for more details about return types
         *
         * @example
         *
         * $eventEngine->registerType(self::USER, self::user());
         */
    }
}
