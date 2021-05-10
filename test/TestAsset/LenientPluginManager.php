<?php

declare(strict_types=1);

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
