<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassAcceptingWellKnownServicesAsConstructorParameters
{
    public function __construct(public ValidatorPluginManager $validators)
    {
    }
}
