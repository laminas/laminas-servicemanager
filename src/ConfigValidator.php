<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\InvalidServiceManagerConfigurationException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

use function array_keys;
use function class_exists;
use function class_implements;
use function get_debug_type;
use function in_array;
use function is_array;
use function is_bool;
use function is_callable;
use function is_string;
use function method_exists;
use function sprintf;

final class ConfigValidator implements ConfigValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function assertIsValidConfiguration(array $config): void
    {
        if (isset($config['abstract_factories'])) {
            $this->assertIsValidAbstractFactoriesConfiguration($config['abstract_factories']);
        }

        if (isset($config['aliases'])) {
            $this->assertIsValidAliasesConfiguration($config['aliases']);
        }

        if (isset($config['delegators'])) {
            $this->assertIsValidDelegatorConfiguration($config['delegators']);
        }

        if (isset($config['factories'])) {
            $this->assertIsValidFactoryConfiguration($config['factories']);
        }

        if (isset($config['initializers'])) {
            $this->assertIsValidInitializerConfiguration($config['initializers']);
        }

        if (isset($config['invokables'])) {
            $this->assertIsValidInvokableConfiguration($config['invokables']);
        }

        if (isset($config['lazy_services'])) {
            $this->assertIsValidLazyServiceConfiguration($config['lazy_services']);
        }

        if (isset($config['services'])) {
            $this->assertIsValidServiceConfiguration($config['services']);
        }

        if (isset($config['shared'])) {
            $this->assertIsValidShareConfiguration($config['shared']);
        }

        if (isset($config['shared_by_default']) && ! is_bool($config['shared_by_default'])) {
            throw new InvalidServiceManagerConfigurationException(
                'Configuration `shared_by_default` has to represent an array map where the service name'
                . ' is mapped with a boolean.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function isValidConfiguration(array $config): bool
    {
        try {
            self::assertIsValidConfiguration($config);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    private function assertIsValidShareConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonArrayConfiguration('shared', $configuration);
        }

        foreach ($configuration as $service => $share) {
            if (! is_string($service)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('shared');
            }

            if (! is_bool($share)) {
                throw new InvalidServiceManagerConfigurationException(
                    'Configuration `share` has to represent an array map where the service name'
                    . ' is mapped with a boolean.'
                );
            }
        }
    }

    private function assertIsValidServiceConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonArrayConfiguration('services', $configuration);
        }

        foreach (array_keys($configuration) as $serviceName) {
            if (! is_string($serviceName)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('services');
            }
        }
    }

    private function assertIsValidLazyServiceConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('lazy_services');
        }

        if (isset($configuration['class_map'])) {
            if (! is_array($configuration['class_map'])) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('lazy_services.class_map');
            }

            /** @var mixed $instanceTarget */
            foreach ($configuration['class_map'] as $serviceName => $instanceTarget) {
                if (! is_string($serviceName)) {
                    throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration(
                        'lazy_services.class_map'
                    );
                }

                if (! is_string($instanceTarget)) {
                    throw new InvalidServiceManagerConfigurationException(sprintf(
                        'Configuration `lazy_services.class_map` value is expected to contain a map of service'
                        . ' names targeting implementations. Expected `class-string`; "%s" given',
                        get_debug_type($instanceTarget)
                    ));
                } elseif (! class_exists($instanceTarget)) {
                    throw new InvalidServiceManagerConfigurationException(sprintf(
                        'Configuration `lazy_services.class_map` value is expected to contain a map of service'
                        . ' names targeting implementations. Expected `class-string` to an existing class; "%s" given',
                        get_debug_type($instanceTarget)
                    ));
                }
            }
        }

        if (isset($configuration['proxies_namespace'])) {
            if (! is_string($configuration['proxies_namespace']) || $configuration['proxies_namespace'] === '') {
                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Configuration `lazy_services.proxies_namespace` is expected to contain a `non-empty-string`'
                    . ' representing a namespace to be used when generating proxies; "%s" given',
                    get_debug_type($configuration['proxies_namespace'])
                ));
            }
        }

        if (isset($configuration['proxies_target_dir'])) {
            if (! is_string($configuration['proxies_target_dir']) || $configuration['proxies_target_dir'] === '') {
                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Configuration `lazy_services.proxies_target_dir` is expected to contain a `non-empty-string`'
                    . ' representing a directory to be used to store files when generating proxies; "%s" given',
                    get_debug_type($configuration['proxies_target_dir'])
                ));
            }
        }

        if (isset($configuration['write_proxy_files']) && ! is_bool($configuration['write_proxy_files'])) {
            throw new InvalidServiceManagerConfigurationException(
                'Configuration `lazy_services.write_proxy_files` has to represent an array map where'
                . ' the service name is mapped with a boolean.'
            );
        }
    }

    private function assertIsValidInvokableConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('invokables');
        }

        /**
         * @var mixed $instanceTarget
         * @var mixed $serviceName
         */
        foreach ($configuration as $serviceName => $instanceTarget) {
            if (! is_string($serviceName)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('invokables');
            }

            if (! is_string($instanceTarget)) {
                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Configuration `invokables` value is expected to contain a map of service'
                    . ' names targeting implementations. Expected `class-string`; "%s" given',
                    get_debug_type($instanceTarget)
                ));
            } elseif (! class_exists($instanceTarget)) {
                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Configuration `invokables` value is expected to contain a map of service'
                    . ' names targeting implementations. Expected `class-string` to an existing class; "%s" given',
                    get_debug_type($instanceTarget)
                ));
            }
        }
    }

    private function assertIsValidInitializerConfiguration(mixed $initializers): void
    {
        if (! is_array($initializers)) {
            throw InvalidServiceManagerConfigurationException::fromNonArrayConfiguration(
                'initializers',
                $initializers
            );
        }

        /** @var mixed $initializer */
        foreach ($initializers as $initializer) {
            if ($initializer instanceof InitializerInterface) {
                continue;
            }

            if (
                is_string($initializer)
                && class_exists($initializer)
                && method_exists($initializer, '__invoke')
            ) {
                // assume class-string providing a class which implements `__invoke` also implements expected signatures
                continue;
            }

            if (is_callable($initializer)) {
                // assume the callable implements expected signatures
                continue;
            }

            throw new InvalidServiceManagerConfigurationException(sprintf(
                'Provided `initializers` configuration has to represent an array of any of:'
                . ' `%1$s` implementations'
                . ', `class-string<%1$s>`'
                . ', `class-string<object&%2$s>`'
                . ', `%2$s`'
                . '; "%3$s" given',
                InitializerInterface::class,
                'callable(ContainerInterface,mixed):void',
                get_debug_type($initializer),
            ));
        }
    }

    private function assertIsValidAliasesConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('aliases');
        }

        /**
         * @var mixed $alias
         * @var mixed $target
         */
        foreach ($configuration as $alias => $target) {
            if (! is_string($alias)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('aliases');
            }

            if (! is_string($target)) {
                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Configuration `aliases` must be a map of alias names to target names and thus,'
                    . ' represent strings; "%s" given',
                    get_debug_type($target)
                ));
            }
        }
    }

    private function assertIsValidDelegatorConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('delegators');
        }

        /**
         * @var mixed $delegators
         * @var mixed $delegatedServiceName
         */
        foreach ($configuration as $delegatedServiceName => $delegators) {
            if (! is_string($delegatedServiceName)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('delegators');
            }

            if (! is_array($delegators)) {
                throw InvalidServiceManagerConfigurationException::fromNonArrayConfiguration(
                    sprintf('delegators.%s', $delegatedServiceName),
                    $delegators
                );
            }

            /**
             * @var mixed $delegator
             */
            foreach ($delegators as $delegator) {
                if ($delegator instanceof DelegatorFactoryInterface) {
                    continue;
                }

                if (
                    is_string($delegator)
                    && class_exists($delegator)
                    && method_exists($delegator, '__invoke')
                ) {
                    // assume class-string providing a class which implements `__invoke`
                    // also implements expected signatures
                    continue;
                }

                if (is_callable($delegator)) {
                    // assume the callable implements expected signatures
                    continue;
                }

                throw new InvalidServiceManagerConfigurationException(sprintf(
                    'Provided `delegators.%1$s` configuration has to represent an array of any of:'
                    . ' `%2$s` implementations'
                    . ', `class-string<%2$s>`'
                    . ', `class-string<object&%3$s>`'
                    . ', `%3$s`'
                    . '; "%4$s" given',
                    $delegatedServiceName,
                    DelegatorFactoryInterface::class,
                    'callable(ContainerInterface,string,callable():mixed,array|null):mixed',
                    get_debug_type($delegator),
                ));
            }
        }
    }

    private function assertIsValidFactoryConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('factories');
        }

        /**
         * @var mixed $factory
         * @var array-key $serviceName
         */
        foreach ($configuration as $serviceName => $factory) {
            if (! is_string($serviceName)) {
                throw InvalidServiceManagerConfigurationException::fromNonMapConfiguration('factories');
            }

            if ($factory instanceof FactoryInterface) {
                continue;
            }

            if (
                is_string($factory)
                && class_exists($factory)
                && method_exists($factory, '__invoke')
            ) {
                // assume class-string providing a class which implements `__invoke` also implements expected signatures
                continue;
            }

            if (is_callable($factory)) {
                // assume the callable implements expected signatures
                continue;
            }

            throw new InvalidServiceManagerConfigurationException(sprintf(
                'Provided `factories.%1$s` configuration has to represent an array of any of:'
                . ' `%2$s` implementations'
                . ', `class-string<%2$s>`'
                . ', `class-string<object&%3$s>`'
                . ', `%3$s`'
                . '; "%4$s" given ',
                $serviceName,
                FactoryInterface::class,
                'callable(ContainerInterface,string,array|null):mixed',
                get_debug_type($factory),
            ));
        }
    }

    private function assertIsValidAbstractFactoriesConfiguration(mixed $configuration): void
    {
        if (! is_array($configuration)) {
            throw InvalidServiceManagerConfigurationException::fromNonArrayConfiguration(
                'abstract_factories',
                $configuration
            );
        }

        /** @var mixed $abstractFactory */
        foreach ($configuration as $abstractFactory) {
            if ($abstractFactory instanceof AbstractFactoryInterface) {
                continue;
            }

            if (
                is_string($abstractFactory)
                && class_exists($abstractFactory)
                && in_array(AbstractFactoryInterface::class, class_implements($abstractFactory), true)
            ) {
                continue;
            }

            throw new InvalidServiceManagerConfigurationException(sprintf(
                'Configuration `abstract_factories` has to provide an array of abstract factories implementing `%1$s`.'
                . ' Both `class-string<%1$s>` and instances of `%1$s` are allowed; "%2$s" given',
                AbstractFactoryInterface::class,
                get_debug_type($abstractFactory),
            ));
        }
    }
}
