<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ContainerConfigTest\TestAsset\Delegator1Factory;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use LaminasTest\ServiceManager\TestAsset\AbstractFactoryFoo;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleInitializer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\Config
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ConfigTest extends TestCase
{
    /**
     * @return array{"array":ServiceManagerConfigurationType,config:ConfigInterface}
     */
    public function testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod(): array
    {
        $expected = [
            'abstract_factories' => [
                AbstractFactoryFoo::class,
            ],
            'aliases'            => [
                'foo' => self::class,
                'bar' => __NAMESPACE__,
            ],
            'delegators'         => [
                'foo' => [
                    Delegator1Factory::class,
                ],
            ],
            'factories'          => [
                'bar' => AbstractFactoryFoo::class,
            ],
            'initializers'       => [
                SimpleInitializer::class,
            ],
            'invokables'         => [
                'foo' => InvokableObject::class,
            ],
            'lazy_services'      => [
                'class_map' => [
                    self::class => self::class,
                ],
            ],
            'services'           => [
                'foo' => $this,
            ],
            'shared'             => [
                self::class   => true,
                __NAMESPACE__ => false,
            ],
        ];

        $config = $expected;

        $services = $this->createMock(ServiceLocatorInterface::class);
        $services
            ->expects(self::once())
            ->method('configure')
            ->with($expected)
            ->willReturnSelf();

        $configuration = new Config($config);
        self::assertEquals($services, $configuration->configureServiceManager($services));

        return [
            'array'  => $expected,
            'config' => $configuration,
        ];
    }

    /**
     * @param array{"array":ServiceManagerConfigurationType,config:ConfigInterface} $dependencies
     * @depends testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod
     */
    public function testToArrayReturnsConfiguration(array $dependencies): void
    {
        $configuration  = $dependencies['array'];
        $configInstance = $dependencies['config'];

        self::assertSame($configuration, $configInstance->toArray());
    }
}
