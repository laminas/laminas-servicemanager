<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;

final class NonAutoInvokablePluginManager extends AbstractSingleInstancePluginManager
{
    protected bool $autoAddInvokableClass = false;

    protected string $instanceOf = InvokableObject::class;
}
