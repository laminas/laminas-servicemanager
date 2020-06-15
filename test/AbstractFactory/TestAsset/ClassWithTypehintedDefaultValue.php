<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

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
