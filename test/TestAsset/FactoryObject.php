<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class FactoryObject
{
    public function __construct(public mixed $dependency)
    {
    }
}
