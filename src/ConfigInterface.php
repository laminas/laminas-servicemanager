<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Psr\Container\ContainerInterface;

/**
 * @see ContainerInterface
 *
 * @psalm-type AbstractFactoriesConfigurationType = array<
 *      array-key,
 *      class-string<Factory\AbstractFactoryInterface>
 *      |Factory\AbstractFactoryInterface
 * >
 * @psalm-type DelegatorCallableType = callable(ContainerInterface,string,callable():mixed,array|null):mixed
 * @psalm-type DelegatorsConfigurationType = array<
 *      string,
 *      array<
 *          array-key,
 *          class-string<Factory\DelegatorFactoryInterface>
 *          |Factory\DelegatorFactoryInterface
 *          |DelegatorCallableType
 *      >
 * >
 * @psalm-type FactoryCallableType = callable(ContainerInterface,string,array|null):mixed
 * @psalm-type FactoriesConfigurationType = array<
 *      string,
 *      class-string<Factory\FactoryInterface>
 *      |Factory\FactoryInterface
 *      |FactoryCallableType
 * >
 * @psalm-type InitializerCallableType = callable(ContainerInterface,mixed):void
 * @psalm-type InitializersConfigurationType = array<
 *      array-key,
 *      class-string<Initializer\InitializerInterface>
 *      |Initializer\InitializerInterface
 *      |InitializerCallableType
 * >
 * @psalm-type LazyServicesConfigurationType = array{
 *      class_map?:array<string,class-string>,
 *      proxies_namespace?:non-empty-string,
 *      proxies_target_dir?:non-empty-string,
 *      write_proxy_files?:bool
 * }
 * @psalm-type ServiceManagerConfigurationType = array{
 *     abstract_factories?: AbstractFactoriesConfigurationType,
 *     aliases?: array<string,string>,
 *     delegators?: DelegatorsConfigurationType,
 *     factories?: FactoriesConfigurationType,
 *     initializers?: InitializersConfigurationType,
 *     invokables?: array<string,class-string>,
 *     lazy_services?: LazyServicesConfigurationType,
 *     services?: array<string,mixed>,
 *     shared?:array<string,bool>,
 *     shared_by_default?: bool,
 *     ...
 * }
 */
interface ConfigInterface
{
    /**
     * Configure a service manager.
     *
     * Implementations should pull configuration from somewhere (typically
     * local properties) and pass it to a ServiceManager's withConfig() method,
     * returning a new instance.
     *
     * @template T of ServiceLocatorInterface
     * @param T $serviceLocator
     * @return T
     */
    public function configureServiceManager(ServiceLocatorInterface $serviceLocator);

    /**
     * Return configuration for a service manager instance as an array.
     *
     * Implementations MUST return an array compatible with ServiceManager::configure,
     * containing one or more of the following keys:
     *
     * - abstract_factories
     * - aliases
     * - delegators
     * - factories
     * - initializers
     * - invokables
     * - lazy_services
     * - services
     * - shared
     *
     * In other words, this should return configuration that can be used to instantiate
     * a service manager or plugin manager, or pass to its `withConfig()` method.
     *
     * @return ServiceManagerConfigurationType
     */
    public function toArray(): array;
}
