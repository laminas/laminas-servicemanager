<?php

declare(strict_types=1);

namespace LaminasBench\ServiceManager\BenchAsset;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class AbstractFactoryFoo implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        if ($requestedName === 'foo') {
            return new Foo($options);
        }
        return false;
    }

    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, string $requestedName): bool
    {
        return $requestedName === 'foo';
    }
}
