<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithScalarDependencyDefiningDefaultValue
{
    public string $foo;

    public function __construct(string $foo = 'bar')
    {
        $this->foo = $foo;
    }
}
