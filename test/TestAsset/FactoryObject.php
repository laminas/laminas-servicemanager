<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class FactoryObject
{
    public $dependency;

    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }
}
