<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class AbstractFactoryFoo implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(containerinterface $container, $requestedName, ?array $options = null)
    {
        if ($requestedName === 'foo') {
            return new Foo($options);
        }

        return false;
    }

    /**
     * @param string $requestedName
     */
    public function canCreate(containerinterface $container, $requestedName): bool
    {
        return $requestedName === 'foo';
    }
}
