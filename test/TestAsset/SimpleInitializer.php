<?php

declare(strict_types=1);

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
