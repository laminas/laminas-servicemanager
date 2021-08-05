<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Delegator factory interface.
 *
 * Defines the capabilities required by a delegator factory. Delegator
 * factories are used to either decorate a service instance, or to allow
 * decorating the instantiation of a service instance (for instance, to
 * provide optional dependencies via setters, etc.).
 */
interface DelegatorFactoryInterface
{
    /**
     * A factory that creates delegates of a given service
     *
     * @param  string             $name
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerException If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null);
}
