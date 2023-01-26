<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class AbstractFactoryFoo implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if ($requestedName === 'foo') {
            return new Foo($options);
        }

        return false;
    }

    /**
     * @param string $requestedName
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        return $requestedName === 'foo';
    }
}
