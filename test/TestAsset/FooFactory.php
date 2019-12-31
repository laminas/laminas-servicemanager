<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FooFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    protected $creationOptions;

    public function __construct(array $creationOptions = array())
    {
        $this->creationOptions = $creationOptions;
    }

    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    public function getCreationOptions()
    {
        return $this->creationOptions;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Foo;
    }
}
