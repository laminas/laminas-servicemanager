<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassAcceptingConfigToConstructor
{
    public $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
