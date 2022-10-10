<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassAcceptingConfigToConstructor
{
    public function __construct(public array $config)
    {
    }
}
