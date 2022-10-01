<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class ComplexDependencyObject
{
    public function __construct(
        SimpleDependencyObject $simpleDependencyObject,
        SecondComplexDependencyObject $secondComplexDependencyObject
    ) {
    }
}
