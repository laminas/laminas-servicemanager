<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\InvalidServiceException;
use Psr\Container\ContainerExceptionInterface;

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
     * @param  object $instance
     * @return void
     * @throws InvalidServiceException If created instance does not respect the
     *     constraint on type imposed by the plugin manager
     * @throws ContainerExceptionInterface&\Throwable if any other error occurs
     */
    public function validate($instance);
}
