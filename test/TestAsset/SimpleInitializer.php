<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use stdClass;

class SimpleInitializer implements InitializerInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (! $instance instanceof stdClass) {
            return;
        }
        $instance->foo = 'bar';
    }
}
