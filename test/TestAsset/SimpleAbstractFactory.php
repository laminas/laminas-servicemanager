<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

final class SimpleAbstractFactory implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, string $name): bool
    {
        return true;
    }

    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, string $className, ?array $options = null): mixed
    {
        if (empty($options)) {
            return new $className();
        }

        return new $className($options);
    }
}
