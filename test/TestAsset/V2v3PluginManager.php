<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use RuntimeException;

use function get_class;
use function sprintf;

class V2v3PluginManager extends AbstractPluginManager
{
    /** @var array<string,string> */
    protected $aliases = [
        'foo' => InvokableObject::class,

        // Legacy Zend Framework aliases
        \ZendTest\ServiceManager\TestAsset\InvokableObject::class => InvokableObject::class,

        // v2 normalized FQCNs
        'zendtestservicemanagertestassetinvokableobject' => InvokableObject::class,
    ];

    /** @var array<string,string> */
    protected $factories = [
        InvokableObject::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution
        'laminastestservicemanagertestassetinvokableobject' => InvokableFactory::class,
    ];

    protected ?string $instanceOf = InvokableObject::class;

    protected bool $shareByDefault = false;

    protected bool $sharedByDefault = false;

    /**
     * @param mixed $plugin
     * @return void
     * @throws InvalidServiceException
     */
    public function validate($plugin)
    {
        if ($plugin instanceof $this->instanceOf) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            "'%s' is not an instance of '%s'",
            get_class($plugin),
            (string) $this->instanceOf
        ));
    }

    /**
     * @param mixed $plugin
     * @return void
     * @throws RuntimeException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
