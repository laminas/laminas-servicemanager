<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class FailingAbstractFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(containerinterface $container, $name)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(containerinterface $container, $className, ?array $options = null)
    {
    }
}
