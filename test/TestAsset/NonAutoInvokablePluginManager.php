<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

class NonAutoInvokablePluginManager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;
    protected $instanceOf = InvokableObject::class;
}
