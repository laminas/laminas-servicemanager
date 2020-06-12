<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class SimpleAbstractFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $name)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $className, array $options = null)
    {
        if (empty($options)) {
            return new $className();
        }

        return new $className($options);
    }
}
