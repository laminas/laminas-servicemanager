<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * Interface for a plugin manager
 *
 * A plugin manager is a specialized service locator used to create homogeneous objects
 */
interface PluginManagerInterface extends ServiceLocatorInterface
{
    /**
     * Validate an instance
     *
     * @param  mixed $instance
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager.
     * @throws ContainerException If any other error occurs.
     */
    public function validate($instance);
}
