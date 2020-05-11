<?php

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;

class SimpleDependencyObjectFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return SimpleDependencyObject
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SimpleDependencyObject($container->get(\LaminasTest\ServiceManager\TestAsset\InvokableObject::class));
    }
}
