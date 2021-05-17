<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithScalarParameters
{
    /** @var string */
    public $foo = 'foo';

    /** @var string */
    public $bar = 'bar';

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
