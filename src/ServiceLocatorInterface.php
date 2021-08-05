<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Interface for service locator
 */
interface ServiceLocatorInterface extends
    PsrContainerInterface,
    InteropContainerInterface
{
    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @param  string $name
     * @return mixed
     * @throws Exception\ServiceNotFoundException If no factory/abstract
     *     factory could be found to create the instance.
     * @throws Exception\ServiceNotCreatedException If factory/delegator fails
     *     to create the instance.
     * @throws ContainerExceptionInterface If any other error occurs.
     */
    public function build($name, ?array $options = null);
}
