<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

use Laminas\ServiceManager\AbstractPluginManager as PluginManager;
use LaminasTest\ServiceManager\TestAsset\Foo;
use stdClass;

final class AbstractPluginManager
{
    /** @param PluginManager<Foo|stdClass> $pluginManager */
    public function pluginManagerRetrievesDefaultType(PluginManager $pluginManager): Foo|stdClass
    {
        return $pluginManager->get('unknown-type-will-result-in-plugin-manager-default-type');
    }

    /** @param PluginManager<Foo|stdClass> $pluginManager */
    public function pluginManagerRetrievesRequestedFooType(PluginManager $pluginManager): Foo
    {
        return $pluginManager->get(Foo::class);
    }

    /** @param PluginManager<Foo|stdClass> $pluginManager */
    public function pluginManagerRetrievesRequestedStdClassType(PluginManager $pluginManager): stdClass
    {
        return $pluginManager->get(stdClass::class);
    }

    /**
     * @see https://github.com/vimeo/psalm/issues/8815
     *
     * @param PluginManager<Foo|stdClass> $pluginManager
     * @psalm-suppress TypeDoesNotContainType this type suppression should be valid.
     */
    public function pluginManagerCannotRetrieveImpossibleType(PluginManager $pluginManager): self
    {
        return $pluginManager->get(self::class);
    }
}
