<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Psr\Container\ContainerInterface;

class LenientPluginManager extends AbstractPluginManager
{
    /**
     * Allow anything to be considered valid.
     */
    public function validate($instance)
    {
        return;
    }

    public function getCreationContext(): ContainerInterface
    {
        return $this->creationContext;
    }
}
