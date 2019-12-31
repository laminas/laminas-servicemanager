<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Config;

class ExtendedConfig extends Config
{
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
