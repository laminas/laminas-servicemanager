<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\InvokableObjectPluginManager;
use PHPUnit\Framework\TestCase;

/**
 * Example test of using CommonPluginManagerTrait
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class ExamplePluginManagerTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager(array $config = []): AbstractSingleInstancePluginManager
    {
        return new InvokableObjectPluginManager(new ServiceManager(), $config);
    }

    protected function getInstanceOf(): string
    {
        return InvokableObject::class;
    }
}
