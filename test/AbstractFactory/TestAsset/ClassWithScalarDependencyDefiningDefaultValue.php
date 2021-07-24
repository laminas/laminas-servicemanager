<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithScalarDependencyDefiningDefaultValue
{
    /** @var string */
    public $foo;

    /**
     * @param string $foo
     */
    public function __construct($foo = 'bar')
    {
        $this->foo = $foo;
    }
}
