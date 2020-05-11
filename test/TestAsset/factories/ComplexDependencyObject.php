<?php

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;

class ComplexDependencyObjectFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ComplexDependencyObject
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ComplexDependencyObject(
            $container->get(\LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject::class),
            $container->get(\LaminasTest\ServiceManager\TestAsset\SecondComplexDependencyObject::class)
        );
    }
}
