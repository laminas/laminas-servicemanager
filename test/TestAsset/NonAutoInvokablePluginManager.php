<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

class NonAutoInvokablePluginManager extends AbstractPluginManager
{
    protected bool $autoAddInvokableClass = false;

    protected ?string $instanceOf = InvokableObject::class;
}
