<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

final class NonAutoInvokablePluginManager extends AbstractPluginManager
{
    protected bool $autoAddInvokableClass = false;

    protected string|null $instanceOf = InvokableObject::class;
}
