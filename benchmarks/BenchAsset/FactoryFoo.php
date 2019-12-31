<?php
namespace LaminasBench\ServiceManager\BenchAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FactoryFoo implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Foo($options);
    }
}
