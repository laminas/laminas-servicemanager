<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class SimpleDependencyObject
{
    public function __construct(InvokableObject $invokableObject)
    {
    }
}
