<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Psr\Container\ContainerInterface;

/**
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

    /** @var array<string,string> */
    protected array $factories = [
        InvokableObject::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution
        'laminastestservicemanagertestassetinvokableobject' => InvokableFactory::class,
    ];

    protected string $instanceOf = InvokableObject::class;

    protected bool $sharedByDefault = false;

    public function __construct(ContainerInterface $creationContext)
    {
        parent::__construct($creationContext, ['aliases' => $this->aliases, 'factories' => $this->factories]);
    }
}
