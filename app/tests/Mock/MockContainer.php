<?php

declare(strict_types=1);

namespace AcmeTest\Mock;

use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_key_exists;

final class MockContainer implements ContainerInterface
{
    /** @var array */
    private $mocks;

    public function __construct(array $mocks)
    {
        $this->mocks = $mocks;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if (! $this->has($id)) {
            throw new RuntimeException("Service $id can't be resolved. No mock provided!");
        }

        return $this->mocks[$id];
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return array_key_exists($id, $this->mocks);
    }
}
