<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithScalarDependencyDefiningDefaultValue
{
    public string $foo;

    /**
     * @param string $foo
     */
    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }
}
