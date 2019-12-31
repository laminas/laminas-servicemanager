<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Mock factory that logs delegated service instances and returns itself instead of the original service
 */
class MockSelfReturningDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @var mixed[]
     */
    public $instances = array();

    /**
     * {@inheritDoc}
     */
    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {
        $this->instances[] = call_user_func($callback);

        return $this;
    }
}
