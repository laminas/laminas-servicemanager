<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;

/**
 * @template-extends AbstractSingleInstancePluginManager<InvokableObject>
 */
final class SimplePluginManager extends AbstractSingleInstancePluginManager
{
    /** @var class-string<InvokableObject> */
    protected string $instanceOf = InvokableObject::class;
}
