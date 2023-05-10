<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Command;

use Laminas\ServiceManager\Tool\ConfigDumperInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal Factories are not meant to be used in any upstream projects.
 */
final class ConfigDumperCommandFactory
{
    public function __invoke(ContainerInterface $container): ConfigDumperCommand
    {
        return new ConfigDumperCommand($container->get(ConfigDumperInterface::class));
    }
}
