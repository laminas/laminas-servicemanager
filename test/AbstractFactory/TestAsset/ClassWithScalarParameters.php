<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithScalarParameters
{
    public function __construct(public string $foo, public string $bar)
    {
    }
}
