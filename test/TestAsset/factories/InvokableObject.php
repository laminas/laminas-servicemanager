<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Interop\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InvokableObjectFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return InvokableObject
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new InvokableObject();
    }
}
