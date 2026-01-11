<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final readonly class ObjectWithObjectScalarDependency
{
    public function __construct(SimpleDependencyObject $simpleDependencyObject, ObjectWithScalarDependency $dependency)
    {
    }
}
