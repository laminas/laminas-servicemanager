<?php
namespace LaminasBench\ServiceManager\BenchAsset;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FactoryFoo implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Foo();
    }
}
