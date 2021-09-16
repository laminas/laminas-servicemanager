<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ValidatorPluginManager;

class ClassAcceptingWellKnownServicesAsConstructorParameters
{
    public ValidatorPluginManager $validators;

    public function __construct(ValidatorPluginManager $validators)
    {
        $this->validators = $validators;
    }
}
