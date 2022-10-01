<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassAcceptingConfigToConstructor
{
    public array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
