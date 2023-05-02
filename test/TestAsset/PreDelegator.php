<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

final class PreDelegator implements DelegatorFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(
        ContainerInterface $container,
        string $name,
        callable $callback,
        ?array $options = null
    ): mixed {
        if (! $container->has('config')) {
            return $callback();
        }

        $config   = $container->get('config');
        $instance = $callback();

        foreach ($config as $key => $value) {
            $instance->{$key} = $value;
        }

        return $instance;
    }
}
