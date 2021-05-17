<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class ObjectWithScalarDependency
{
    /**
     * @param mixed $aName
     * @param mixed $aValue
     */
    public function __construct($aName, $aValue)
    {
    }
}
