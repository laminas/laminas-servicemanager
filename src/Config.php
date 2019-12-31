<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager;

use Laminas\Stdlib\ArrayUtils;

class Config implements ConfigInterface
{
    /**
     * Allowed configuration keys
     *
     * @var array
     */
    protected $allowedKeys = [
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

    /**
     * @var array
     */
    protected $config = [
        'abstract_factories' => [],
        'aliases'            => [],
        'delegators'         => [],
        'factories'          => [],
        'initializers'       => [],
        'invokables'         => [],
        'lazy_services'      => [],
        'services'           => [],
        'shared'             => [],
    ];

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Only merge keys we're interested in
        foreach (array_keys($config) as $key) {
            if (! isset($this->allowedKeys[$key])) {
                unset($config[$key]);
            }
        }

        $this->config = ArrayUtils::merge($this->config, $config);
    }

    /**
     * Configure service manager
     *
     * @param ServiceManager $serviceManager
     * @return ServiceManager Returns the updated service manager instance.
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        return $serviceManager->configure($this->config);
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->config;
    }
}
