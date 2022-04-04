<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractFactoryFoo implements AbstractFactoryInterface
{
    /**
     * @param string                   $requestedName
     * @param array<string,mixed>|null $options
     * @return Foo|false
     */
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
