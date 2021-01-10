<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Interop\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use Laminas\ServiceManager\Factory\FactoryInterface;

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
