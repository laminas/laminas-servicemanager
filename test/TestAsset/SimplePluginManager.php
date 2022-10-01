<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

final class SimplePluginManager extends AbstractPluginManager
{
    /** @var string */
    protected $instanceOf = InvokableObject::class;
}
