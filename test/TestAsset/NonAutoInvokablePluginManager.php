<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

class NonAutoInvokablePluginManager extends AbstractPluginManager
{
    /** @var bool */
    protected $autoAddInvokableClass = false;

    /** @var string */
    protected $instanceOf = InvokableObject::class;
}
