<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class SimpleAbstractFactory implements AbstractFactoryInterface
{
    /** {@inheritDoc} */
    public function canCreate(containerinterface $container, $name)
    {
        return true;
    }

    /** {@inheritDoc} */
    public function __invoke(containerinterface $container, $className, ?array $options = null)
    {
        if (empty($options)) {
            return new $className();
        }

        return new $className($options);
    }
}
