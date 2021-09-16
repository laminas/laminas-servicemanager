<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassAcceptingWellKnownServicesAsConstructorParameters
{
    public \LaminasTest\ServiceManager\AbstractFactory\TestAsset\ValidatorPluginManager $validators;

    public function __construct(ValidatorPluginManager $validators)
    {
        $this->validators = $validators;
    }
}
