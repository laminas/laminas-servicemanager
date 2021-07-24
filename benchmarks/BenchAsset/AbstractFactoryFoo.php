<?php

namespace LaminasBench\ServiceManager\BenchAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractFactoryFoo implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if ($requestedName === 'foo') {
            return new Foo($options);
        }
        return false;
    }

    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return $requestedName === 'foo';
    }
}
