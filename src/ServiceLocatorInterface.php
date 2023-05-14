<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Interface for service locator
 */
interface ServiceLocatorInterface extends ContainerInterface
{
    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @template T of object
     * @param  string|class-string<T> $name
     * @psalm-return ($name is class-string<T> ? T : mixed)
     * @throws ServiceNotFoundException If no factory/abstract
     *     factory could be found to create the instance.
     * @throws ServiceNotCreatedException If factory/delegator fails
     *     to create the instance.
     * @throws ContainerExceptionInterface If any other error occurs.
     */
    public function build(string $name, ?array $options = null): mixed;
}
