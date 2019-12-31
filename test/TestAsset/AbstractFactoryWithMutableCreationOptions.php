<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use stdClass;

/**
 * implements multiple interface mock
 */
class AbstractFactoryWithMutableCreationOptions implements
    AbstractFactoryInterface,
    MutableCreationOptionsInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return true;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new stdClass;
    }

    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }
}
