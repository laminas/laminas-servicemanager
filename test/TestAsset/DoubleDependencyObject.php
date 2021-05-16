<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class DoubleDependencyObject
{
    public function __construct(InvokableObject $anInvokableObject, InvokableObject $anotherInvokableObject)
    {
    }
}
