<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Psr\Container\ContainerInterface;

final class LenientPluginManager extends AbstractPluginManager
{
    /**
     * Allow anything to be considered valid.
     *
     * @param mixed $instance
     */
    public function validate($instance): void
    {
    }

    public function getCreationContext(): ContainerInterface
    {
        return $this->creationContext;
    }
}
