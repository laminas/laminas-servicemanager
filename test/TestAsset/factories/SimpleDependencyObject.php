<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Interop\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SimpleDependencyObjectFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     * @param null|array $options
     * @return SimpleDependencyObject
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SimpleDependencyObject($container->get(\LaminasTest\ServiceManager\TestAsset\InvokableObject::class));
    }
}
