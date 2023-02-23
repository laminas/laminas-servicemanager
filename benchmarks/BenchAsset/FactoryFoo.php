<?php

declare(strict_types=1);

namespace LaminasBench\ServiceManager\BenchAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FactoryFoo implements FactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        return new Foo($options);
    }
}
