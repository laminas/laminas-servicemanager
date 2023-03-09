<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;

final class SimplePluginManager extends AbstractSingleInstancePluginManager
{
    protected string $instanceOf = InvokableObject::class;
}
