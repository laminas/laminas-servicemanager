<?php

namespace LaminasTest\ServiceManager\TestAsset;

use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ComplexDependencyObjectFactory implements FactoryInterface
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
