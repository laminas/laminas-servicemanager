<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

/**
 * @psalm-require-extends ServiceManager
 */
abstract class AbstractTypedContainerImplementation implements ContainerInterface
{
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @psalm-param string|class-string $id
     */
    public function has(string $id): bool
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->hasService($id);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @psalm-param string|class-string $id
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @return mixed Entry.
     */
    public function get(string $id)
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->getService($id);
    }
}
