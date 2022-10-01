<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Config;

final class ExtendedConfig extends Config
{
    /** @var array */
    protected $config = [
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
