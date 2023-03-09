<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Psr\Container\ContainerExceptionInterface;

/**
 * Interface for a plugin manager
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 *
 * @template InstanceType
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance
     *
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager.
     * @throws ContainerExceptionInterface If any other error occurs.
     * @psalm-assert InstanceType $instance
     */
    public function validate(mixed $instance): void;

    public function has(string $id): bool;

    /**
     * @param class-string<InstanceType>|string $id Service name of plugin to retrieve.
     * @psalm-return ($id is class-string<InstanceType> ? InstanceType : mixed)
     * @throws Exception\ServiceNotFoundException If the manager does not have
     *     a service definition for the instance, and the service is not
     *     auto-invokable.
     * @throws InvalidServiceException If the plugin created is invalid for the
     *     plugin context.
     */
    public function get(string $id): mixed;

    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @param  string|class-string<InstanceType> $name
     * @psalm-return ($name is class-string<InstanceType> ? InstanceType : mixed)
     * @throws Exception\ServiceNotFoundException If no factory/abstract
     *     factory could be found to create the instance.
     * @throws Exception\ServiceNotCreatedException If factory/delegator fails
     *     to create the instance.
     * @throws ContainerExceptionInterface If any other error occurs.
     */
    public function build(string $name, ?array $options = null): mixed;
}
