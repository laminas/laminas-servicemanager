<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest;

use Psr\Container\ContainerInterface;

use function assert;

final class TargetObjectDelegator
{
    public const DELEGATED_VALUE = 'Delegated Value';

    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback)
    {
        $service = $callback();
        assert($service instanceof TargetObject);

        $service->value = self::DELEGATED_VALUE;

        return $service;
    }
}
