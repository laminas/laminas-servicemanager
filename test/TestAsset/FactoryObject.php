<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class FactoryObject
{
    /** @var mixed */
    public $dependency;

    /**
     * @param mixed $dependency
     */
    public function __construct($dependency)
    {
        $this->dependency = $dependency;
    }
}
