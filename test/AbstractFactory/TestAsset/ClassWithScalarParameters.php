<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithScalarParameters
{
    public string $foo = 'foo';

    public string $bar = 'bar';

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
