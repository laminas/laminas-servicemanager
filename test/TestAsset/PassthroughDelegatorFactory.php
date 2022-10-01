<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

final class PassthroughDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\DelegatorFactoryInterface::__invoke()
     */
    public function __invoke(containerinterface $container, $name, callable $callback, ?array $options = null)
    {
        return $callback();
    }
}
