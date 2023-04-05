<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

use function class_exists;

/**
 * @internal
 */
final class ConfigDumperFactory
{
    public function __invoke(ContainerInterface $container): ConfigDumperInterface
    {
        if ($this->isCommandExecutedInMezzioApplication()) {
            return new ConfigDumper($container, ConfigDumper::MEZZIO_CONTAINER_CONFIGURATION);
        }

        return new ConfigDumper($container, ConfigDumper::LAMINAS_MVC_SERVICEMANAGER_CONFIGURATION);
    }

    private function isCommandExecutedInMezzioApplication(): bool
    {
        return class_exists(Application::class);
    }
}
