<?php

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;

class InvokableObjectFactory
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
