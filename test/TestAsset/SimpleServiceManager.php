<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use stdClass;

final class SimpleServiceManager extends ServiceManager
{
    /** @var array<string,string> */
    protected array $factories = [
        stdClass::class => InvokableFactory::class,
    ];
}
