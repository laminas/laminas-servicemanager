<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class ObjectWithScalarDependency
{
    public function __construct(mixed $aName, mixed $aValue)
    {
    }
}
