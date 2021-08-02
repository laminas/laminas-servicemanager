<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Interface for a factory
 *
 * A factory is an callable object that is able to create an object. It is
 * given the instance of the service locator, the requested name of the class
 * you want to create, and any additional options that could be used to
 * configure the instance state.
 */
interface FactoryInterface
{
    /**
     * Create an object
     *
     * @param  string             $requestedName
     * @param  null|array<mixed>  $options
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerException If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null);
}
