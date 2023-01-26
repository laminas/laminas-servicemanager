<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

final class PassthroughDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see \Laminas\ServiceManager\Factory\DelegatorFactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, ?array $options = null)
    {
        return $callback();
    }
}
