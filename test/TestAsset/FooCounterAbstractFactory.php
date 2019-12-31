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
 * Abstract factory that keeps track of the number of times it is instantiated
 */
class FooCounterAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var int
     */
    public static $instantiationCount = 0;

    /**
     * Increments instantiation count
     */
    public function __construct()
    {
        static::$instantiationCount += 1;
    }

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($name == 'foo') {
            return true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new Foo;
    }
}
