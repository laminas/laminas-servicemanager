<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ComplexDependencyObjectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ComplexDependencyObject
    {
        return new ComplexDependencyObject(
            $container->get(\LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject::class),
            $container->get(\LaminasTest\ServiceManager\TestAsset\SecondComplexDependencyObject::class)
        );
    }
}
