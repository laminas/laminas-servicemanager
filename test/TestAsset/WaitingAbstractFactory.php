<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use stdClass;

class WaitingAbstractFactory implements AbstractFactoryInterface
{
    public $waitingService = null;

    public $canCreateCallCount = 0;

    public $createNullService = false;

    public $throwExceptionWhenCreate = false;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $this->canCreateCallCount++;
        return $requestedName === $this->waitingService;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($this->throwExceptionWhenCreate) {
            throw new FooException('E');
        }
        if ($this->createNullService) {
            return null;
        }
        return new stdClass;
    }
}
