<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Psr\Container\ContainerInterface;

use function class_exists;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Abstract plugin manager.
 *
 * Abstract PluginManagerInterface implementation providing:
 *
 * - creation context support. The constructor accepts the parent container
 *   instance, which is then used when creating instances.
 * - plugin validation. Implementations may define the `$instanceOf` property
 *   to indicate what class types constitute valid plugins, omitting the
 *   requirement to define the `validate()` method.
 *
 * @template InstanceType
 * @template-implements PluginManagerInterface<InstanceType>
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-import-type FactoryCallableType from ConfigInterface
 * @psalm-import-type DelegatorCallableType from ConfigInterface
 * @psalm-import-type InitializerCallableType from ConfigInterface
 */
abstract class AbstractPluginManager implements PluginManagerInterface
{
    /**
     * Whether or not to auto-add a FQCN as an invokable if it exists.
     */
    protected bool $autoAddInvokableClass = true;

    protected bool $sharedByDefault = true;

    private ServiceManager $plugins;

    /**
     * @param ServiceManagerConfigurationType $config
     */
    public function __construct(
        ContainerInterface $creationContext,
        array $config = [],
    ) {
        $this->plugins = new ServiceManager([
            'shared_by_default' => $this->sharedByDefault,
        ], $creationContext);

        // TODO: support old, internal, servicemanager properties in constructor to register services from properties
        $this->configure($config);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(array $config): static
    {
        if (isset($config['services'])) {
            /** @psalm-suppress MixedAssignment */
            foreach ($config['services'] as $service) {
                $this->validate($service);
            }
        }

        /** @psalm-var ServiceManagerConfigurationType $config */
        $this->plugins->configure($config);

        return $this;
    }

    /**
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param string|class-string<InstanceType> $name
     * @param InstanceType $service
     */
    public function setService(string $name, mixed $service): void
    {
        $this->validate($service);
        $this->plugins->setService($name, $service);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id): mixed
    {
        if (! $this->has($id)) {
            if (! $this->autoAddInvokableClass || ! class_exists($id)) {
                throw new Exception\ServiceNotFoundException(sprintf(
                    'A plugin by the name "%s" was not found in the plugin manager %s',
                    $id,
                    static::class
                ));
            }

            $this->plugins->setFactory($id, Factory\InvokableFactory::class);
        }

        /** @psalm-suppress MixedAssignment Yes indeed, service managers can return mixed. */
        $instance = $this->plugins->get($id);
        $this->validate($instance);
        /** @psalm-suppress MixedReturnStatement Yes indeed, plugin managers can return mixed. */
        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return $this->plugins->has($id);
    }

    /**
     * {@inheritDoc}
     */
    public function build(string $name, ?array $options = null): mixed
    {
        /** @psalm-suppress MixedAssignment Yes indeed, service managers can return mixed. */
        $plugin = $this->plugins->build($name, $options);
        $this->validate($plugin);

        /** @psalm-suppress MixedReturnStatement Yes indeed, service managers can return mixed. */
        return $plugin;
    }

    /**
     * Add an alias.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @throws ContainerModificationsNotAllowedException If $alias already
     *     exists as a service and overrides are disallowed.
     */
    public function setAlias(string $alias, string $target): void
    {
        $this->plugins->setAlias($alias, $target);
    }

    /**
     * Add an invokable class mapping.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param null|string $class Class to which to map; if omitted, $name is
     *     assumed.
     * @throws ContainerModificationsNotAllowedException If $name already
     *     exists as a service and overrides are disallowed.
     */
    public function setInvokableClass(string $name, string|null $class = null): void
    {
        $this->plugins->setInvokableClass($name, $class);
    }

    /**
     * Specify a factory for a given service name.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param class-string<Factory\FactoryInterface>|FactoryCallableType|Factory\FactoryInterface $factory
     * @throws ContainerModificationsNotAllowedException If $name already
     *     exists as a service and overrides are disallowed.
     */
    public function setFactory(string $name, string|callable|Factory\FactoryInterface $factory): void
    {
        $this->plugins->setFactory($name, $factory);
    }

    /**
     * Create a lazy service mapping to a class.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param null|string $class Class to which to map; if not provided, $name
     *     will be used for the mapping.
     */
    public function mapLazyService(string $name, string|null $class = null): void
    {
        $this->plugins->mapLazyService($name, $class);
    }

    /**
     * Add an abstract factory for resolving services.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param string|AbstractFactoryInterface $factory Abstract factory
     *     instance or class name.
     * @psalm-param class-string<AbstractFactoryInterface>|AbstractFactoryInterface $factory
     */
    public function addAbstractFactory(string|AbstractFactoryInterface $factory): void
    {
        $this->plugins->addAbstractFactory($factory);
    }

    /**
     * Add a delegator for a given service.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param string $name Service name
     * @param string|callable|DelegatorFactoryInterface $factory Delegator
     *     factory to assign.
     * @psalm-param class-string<DelegatorFactoryInterface>|DelegatorCallableType $factory
     */
    public function addDelegator(string $name, string|callable|DelegatorFactoryInterface $factory): void
    {
        $this->plugins->addDelegator($name, $factory);
    }

    /**
     * Add an initializer.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @psalm-param class-string<InitializerInterface>|InitializerCallableType|InitializerInterface $initializer
     */
    public function addInitializer(string|callable|InitializerInterface $initializer): void
    {
        $this->plugins->addInitializer($initializer);
    }

    /**
     * Add a service sharing rule.
     *
     * @deprecated Please use {@see PluginManagerInterface::configure()} instead.
     *
     * @param bool $flag Whether or not the service should be shared.
     * @throws ContainerModificationsNotAllowedException If $name already
     *     exists as a service and overrides are disallowed.
     */
    public function setShared(string $name, bool $flag): void
    {
        $this->plugins->setShared($name, $flag);
    }

    public function getAllowOverride(): bool
    {
        return $this->plugins->getAllowOverride();
    }

    public function setAllowOverride(bool $flag): void
    {
        $this->plugins->setAllowOverride($flag);
    }
}
