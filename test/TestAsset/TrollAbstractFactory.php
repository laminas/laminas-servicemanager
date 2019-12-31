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

class TrollAbstractFactory implements AbstractFactoryInterface
{
    public $inexistingServiceCheckResult = null;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // Check if a non-existing service exists
        $this->inexistingServiceCheckResult = $serviceLocator->has('NonExistingService');

        if ($requestedName === 'SomethingThatCanBeCreated') {
            return true;
        }

        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new stdClass;
    }
}
