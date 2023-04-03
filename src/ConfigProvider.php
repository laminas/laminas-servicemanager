<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Command\ConfigDumperCommand;
use Laminas\ServiceManager\Command\FactoryCreatorCommand;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Tool\ConfigDumperFactory;
use Laminas\ServiceManager\Tool\ConfigDumperInterface;
use Laminas\ServiceManager\Tool\FactoryCreator;
use Laminas\ServiceManager\Tool\FactoryCreatorInterface;
use Symfony\Component\Console\Command\Command;

use function class_exists;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigProvider
{
    /**
     * @return array{
     *  dependencies: ServiceManagerConfigurationType,
     *  ...
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getServiceDependencies(),
            'laminas-cli'  => $this->getLaminasCliDependencies(),
        ];
    }

    /**
     * @return ServiceManagerConfigurationType
     */
    public function getServiceDependencies(): array
    {
        $factories = [
            ConfigDumperInterface::class   => ConfigDumperFactory::class,
            FactoryCreatorInterface::class => static fn (): FactoryCreatorInterface => new FactoryCreator(),
        ];

        if (class_exists(Command::class)) {
            $factories += [
                ConfigDumperCommand::class   => InvokableFactory::class,
                FactoryCreatorCommand::class => InvokableFactory::class,
            ];
        }

        return [
            'factories' => $factories,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function getLaminasCliDependencies(): array
    {
        if (! class_exists(Command::class)) {
            return [];
        }

        return [
            'commands' => [
                ConfigDumperCommand::NAME   => ConfigDumperCommand::class,
                FactoryCreatorCommand::NAME => FactoryCreatorCommand::class,
            ],
        ];
    }
}
