<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use RuntimeException;

class FailingFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(containerinterface $container, $requestedName, ?array $options = null)
    {
        throw new RuntimeException('There is an error');
    }
}
