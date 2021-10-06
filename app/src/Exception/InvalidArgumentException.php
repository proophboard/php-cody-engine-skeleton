<?php

declare(strict_types=1);

namespace Acme\Exception;

use EventEngine\Messaging\Message;
use InvalidArgumentException as PhpInvalidArgumentException;

class InvalidArgumentException extends PhpInvalidArgumentException implements AcmeException
{
    public static function noGenericSchemaMessage(Message $message): InvalidArgumentException
    {
        return new self('No GenericSchemaMessage provided, can not handle message: ' . $message->messageName());
    }
}
