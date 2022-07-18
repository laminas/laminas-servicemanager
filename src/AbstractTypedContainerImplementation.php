<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * This class is not meant to be used outside this component.
 * We only provide this class to polyfill both `psr/container` v1 & v2.
 *
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
        return $this->getService($id);
    }

    /**
     * @internal
     *
     * @psalm-param string|class-string $name
     */
    abstract protected function hasService(string $name): bool;

    /**
     * @internal
     *
     * @psalm-param string|class-string $name
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @return mixed
     */
    abstract protected function getService(string $name);
}
