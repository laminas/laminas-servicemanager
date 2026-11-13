<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final readonly class DoubleDependencyObject
{
    public function __construct(InvokableObject $anInvokableObject, InvokableObject $anotherInvokableObject)
    {
    }
}
