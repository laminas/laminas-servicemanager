<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Initializer\InitializerInterface;
use Psr\Container\ContainerInterface;
use stdClass;

final class SimpleInitializer implements InitializerInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (! $instance instanceof stdClass) {
            return;
        }

        $instance->foo = 'bar';
    }
}
