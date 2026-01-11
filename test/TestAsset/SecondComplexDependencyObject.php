<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final readonly class SecondComplexDependencyObject
{
    public function __construct(InvokableObject $invokableObject)
    {
    }
}
