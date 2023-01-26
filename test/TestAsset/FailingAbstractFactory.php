<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class FailingAbstractFactory implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, $name)
    {
        return false;
    }

    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $className, ?array $options = null)
    {
    }
}
