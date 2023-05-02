<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 * @psalm-import-type FactoriesConfiguration from ServiceManager
 * @template-extends AbstractPluginManager<InvokableObject>
 */
final class InvokableObjectPluginManager extends AbstractSingleInstancePluginManager
{
    /** @var array<string,string> */
    protected array $aliases = [
        'foo' => InvokableObject::class,

        // v2 normalized FQCNs
        'laminastestservicemanagertestassetinvokableobject' => InvokableObject::class,
    ];

    /** @var FactoriesConfiguration */
    protected array $factories = [
        InvokableObject::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution
        'laminastestservicemanagertestassetinvokableobject' => InvokableFactory::class,
    ];

    protected string $instanceOf = InvokableObject::class;

    protected bool $sharedByDefault = false;
}
