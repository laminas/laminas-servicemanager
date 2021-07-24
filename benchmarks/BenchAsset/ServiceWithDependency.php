<?php

namespace LaminasBench\ServiceManager\BenchAsset;

class ServiceWithDependency
{
    /** @var Dependency */
    protected $dependency;

    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
