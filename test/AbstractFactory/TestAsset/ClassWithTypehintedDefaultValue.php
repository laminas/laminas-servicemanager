<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

use ArrayAccess;

class ClassWithTypehintedDefaultValue
{
    public $value;

    public function __construct(ArrayAccess $value = null)
    {
        $this->value = null;
    }
}
