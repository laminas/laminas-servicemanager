<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithScalarDependencyDefiningDefaultValue
{
    public function __construct(public string $foo = 'bar')
    {
    }
}
