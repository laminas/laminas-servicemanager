<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-require-extends ServiceManager
 */
abstract class TypedAbstractContainerImplementation implements ContainerInterface
{
    public function has(string $id): bool
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->hasService($id);
    }

    public function get(string $id)
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->getService($id);
    }
}
