<?php

namespace LaminasBench\ServiceManager\BenchAsset;

class ServiceWithDependency
{
    /**
     * @var Dependency
     */
    private $dependency;

    /**
     * @param Dependency $dependency
     */
    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
}
