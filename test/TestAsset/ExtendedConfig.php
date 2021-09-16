<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Config;

class ExtendedConfig extends Config
{
    protected array $config = [
        'invokables' => [
            InvokableObject::class => InvokableObject::class,
        ],
        'delegators' => [
            'foo' => [
                InvokableObject::class,
            ],
        ],
    ];
}
