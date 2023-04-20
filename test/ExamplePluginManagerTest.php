<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\ServiceManager\Test\CommonPluginManagerTrait;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\InvokableObjectPluginManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Example test of using CommonPluginManagerTrait
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
final class ExamplePluginManagerTest extends TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @param ServiceManagerConfiguration $config
     */
    protected function getPluginManager(array $config = []): AbstractPluginManager
    {
        return new InvokableObjectPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException(): string
    {
        return RuntimeException::class;
    }

    protected function getInstanceOf(): string
    {
        return InvokableObject::class;
    }
}
