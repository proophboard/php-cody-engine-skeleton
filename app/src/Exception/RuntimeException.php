<?php

declare(strict_types=1);

namespace Acme\Exception;

use RuntimeException as PhpRuntimeException;

class RuntimeException extends PhpRuntimeException implements AcmeException
{
}
