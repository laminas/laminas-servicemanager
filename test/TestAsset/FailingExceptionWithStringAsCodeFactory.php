<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FailingExceptionWithStringAsCodeFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(containerinterface $container, $requestedName, ?array $options = null)
    {
        throw new ExceptionWithStringAsCodeException('There is an error');
    }
}
