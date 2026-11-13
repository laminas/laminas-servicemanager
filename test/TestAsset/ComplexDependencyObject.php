<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final readonly class ComplexDependencyObject
{
    public function __construct(
        SimpleDependencyObject $simpleDependencyObject,
        SecondComplexDependencyObject $secondComplexDependencyObject
    ) {
    }
}
