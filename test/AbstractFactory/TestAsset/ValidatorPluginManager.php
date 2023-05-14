<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

class ValidatorPluginManager extends AbstractPluginManager
{
    public function validate(mixed $instance): void
    {
    }
}
