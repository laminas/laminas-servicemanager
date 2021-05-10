<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class SimpleDependencyObject
{
    public function __construct(InvokableObject $invokableObject)
    {
    }
}
