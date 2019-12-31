<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function get_class;
use function sprintf;

class V2v3PluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'foo' => InvokableObject::class,

        // Legacy Zend Framework aliases
        \ZendTest\ServiceManager\TestAsset\InvokableObject::class => InvokableObject::class,

        // v2 normalized FQCNs
        'zendtestservicemanagertestassetinvokableobject' => InvokableObject::class,
    ];

    protected $factories = [
        InvokableObject::class                           => InvokableFactory::class,
        // Legacy (v2) due to alias resolution
        'laminastestservicemanagertestassetinvokableobject' => InvokableFactory::class,
    ];

    protected $instanceOf = InvokableObject::class;

    protected $shareByDefault = false;

    protected $sharedByDefault = false;


    public function validate($plugin)
    {
        if ($plugin instanceof $this->instanceOf) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            "'%s' is not an instance of '%s'",
            get_class($plugin),
            $this->instanceOf
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
