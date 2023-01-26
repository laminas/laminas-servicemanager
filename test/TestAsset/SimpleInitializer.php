<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
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
