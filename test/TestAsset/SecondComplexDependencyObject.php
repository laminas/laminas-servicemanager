<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class SecondComplexDependencyObject
{
    public function __construct(InvokableObject $invokableObject)
    {
    }
}
