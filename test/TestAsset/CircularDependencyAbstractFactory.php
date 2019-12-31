<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class used to try to simulate a cyclic dependency in ServiceManager.
 */
class CircularDependencyAbstractFactory implements AbstractFactoryInterface
{
    public $expectedInstance = 'a retrieved value';

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator->has($name)) {
            return $serviceLocator->get($name);
        }

        return $this->expectedInstance;
    }
}
