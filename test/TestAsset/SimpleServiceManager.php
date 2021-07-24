<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use stdClass;

class SimpleServiceManager extends ServiceManager
{
    /** @var array<string,string> */
    protected $factories = [
        stdClass::class => InvokableFactory::class,
    ];
}
