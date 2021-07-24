<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassAcceptingWellKnownServicesAsConstructorParameters
{
    /** @var ValidatorPluginManager */
    public $validators;

    public function __construct(ValidatorPluginManager $validators)
    {
        $this->validators = $validators;
    }
}
