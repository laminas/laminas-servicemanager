<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

/**
 * Object for defining configuration and configuring an existing service manager instance.
 *
 * In order to provide configuration merging capabilities, this class implements
 * the same functionality as `Laminas\Stdlib\ArrayUtils::merge()`. That routine
 * allows developers to specifically shape how values are merged:
 *
 * - A value which is an instance of `MergeRemoveKey` indicates the value should
 *   be removed during merge.
 * - A value that is an instance of `MergeReplaceKeyInterface` indicates that the
 *   value it contains should be used to replace any previous versions.
 *
 * These features are advanced, and not typically used. If you wish to use them,
 * you will need to require the laminas-stdlib package in your application.
 *
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class Config implements ConfigInterface
{
    private const ALLOWED_CONFIGURATION_KEYS = [
        'abstract_factories' => true,
        'aliases'            => true,
        'delegators'         => true,
        'factories'          => true,
        'initializers'       => true,
        'invokables'         => true,
        'lazy_services'      => true,
        'services'           => true,
        'shared'             => true,
    ];

    /** @var ServiceManagerConfigurationType */
    private array $config;

    /**
     * @param ServiceManagerConfigurationType $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function configureServiceManager(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->configure($this->config);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
