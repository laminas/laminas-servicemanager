<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SimpleDependencyObjectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): SimpleDependencyObject
    {
        return new SimpleDependencyObject($container->get(\LaminasTest\ServiceManager\TestAsset\InvokableObject::class));
    }
}
