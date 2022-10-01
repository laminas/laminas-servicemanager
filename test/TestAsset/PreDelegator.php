<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

final class PreDelegator implements DelegatorFactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(containerinterface $container, $name, callable $callback, ?array $options = null)
    {
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
