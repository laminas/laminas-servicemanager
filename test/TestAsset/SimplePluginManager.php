<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

final class SimplePluginManager extends AbstractPluginManager
{
    protected string|null $instanceOf = InvokableObject::class;
}
