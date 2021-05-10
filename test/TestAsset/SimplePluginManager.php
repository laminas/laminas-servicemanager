<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

class SimplePluginManager extends AbstractPluginManager
{
    protected $instanceOf = InvokableObject::class;
}
