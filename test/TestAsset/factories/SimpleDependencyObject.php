<?php

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use Psr\Container\ContainerInterface;

class SimpleDependencyObjectFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SimpleDependencyObject
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SimpleDependencyObject($container->get(InvokableObject::class));
    }
}
