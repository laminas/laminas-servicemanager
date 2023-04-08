<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Command\AheadOfTimeFactoryCreatorCommand;
use Laminas\ServiceManager\Command\AheadOfTimeFactoryCreatorCommandFactory;
use Laminas\ServiceManager\Command\ConfigDumperCommand;
use Laminas\ServiceManager\Command\FactoryCreatorCommand;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompilerFactory;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompilerInterface;
use Laminas\ServiceManager\Tool\ConfigDumperFactory;
use Laminas\ServiceManager\Tool\ConfigDumperInterface;
use Laminas\ServiceManager\Tool\ConstructorParameterResolver;
use Laminas\ServiceManager\Tool\ConstructorParameterResolverInterface;
use Laminas\ServiceManager\Tool\FactoryCreatorFactory;
use Laminas\ServiceManager\Tool\FactoryCreatorInterface;
use Symfony\Component\Console\Command\Command;

use function class_exists;

/**
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigProvider
{
    public const CONFIGURATION_KEY_FACTORY_TARGET_PATH = 'aot-factory-target-path';

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
            ConfigDumperInterface::class                 => ConfigDumperFactory::class,
            FactoryCreatorInterface::class               => FactoryCreatorFactory::class,
            AheadOfTimeFactoryCompilerInterface::class   => AheadOfTimeFactoryCompilerFactory::class,
            ConstructorParameterResolverInterface::class => static fn (): ConstructorParameterResolverInterface
            => new ConstructorParameterResolver(),
        ];

        if (class_exists(Command::class)) {
            $factories += [
                AheadOfTimeFactoryCreatorCommand::class => AheadOfTimeFactoryCreatorCommandFactory::class,
                ConfigDumperCommand::class              => InvokableFactory::class,
                FactoryCreatorCommand::class            => InvokableFactory::class,
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
                ConfigDumperCommand::NAME              => ConfigDumperCommand::class,
                FactoryCreatorCommand::NAME            => FactoryCreatorCommand::class,
                AheadOfTimeFactoryCreatorCommand::NAME => AheadOfTimeFactoryCreatorCommand::class,
            ],
        ];
    }
}