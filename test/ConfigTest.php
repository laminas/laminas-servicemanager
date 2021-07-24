<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @covers Laminas\ServiceManager\Config
 */
class ConfigTest extends TestCase
{
    use ProphecyTrait;

    public function testMergeArrays(): void
    {
        $config = [
            'invokables' => [
                'foo' => TestAsset\InvokableObject::class,
            ],
            'delegators' => [
                'foo' => [
                    TestAsset\PreDelegator::class,
                ],
            ],
            'factories'  => [
                'service' => TestAsset\FactoryObject::class,
            ],
        ];

        $configuration = new TestAsset\ExtendedConfig($config);
        $result        = $configuration->toArray();

        $expected = [
            'invokables' => [
                'foo'                            => TestAsset\InvokableObject::class,
                TestAsset\InvokableObject::class => TestAsset\InvokableObject::class,
            ],
            'delegators' => [
                'foo' => [
                    TestAsset\InvokableObject::class,
                    TestAsset\PreDelegator::class,
                ],
            ],
            'factories'  => [
                'service' => TestAsset\FactoryObject::class,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod(): array
    {
        $expected = [
            'abstract_factories' => [
                self::class,
                __NAMESPACE__,
            ],
            'aliases'            => [
                'foo' => self::class,
                'bar' => __NAMESPACE__,
            ],
            'delegators'         => [
                'foo' => [
                    self::class,
                    __NAMESPACE__,
                ],
            ],
            'factories'          => [
                'foo' => self::class,
                'bar' => __NAMESPACE__,
            ],
            'initializers'       => [
                self::class,
                __NAMESPACE__,
            ],
            'invokables'         => [
                'foo' => self::class,
                'bar' => __NAMESPACE__,
            ],
            'lazy_services'      => [
                'class_map' => [
                    self::class   => self::class,
                    __NAMESPACE__ => __NAMESPACE__,
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

        $config = $expected + [
            'foo' => 'bar',
            'baz' => 'bat',
        ];

        $services = $this->prophesize(ServiceManager::class);
        $services->configure($expected)->willReturn('CALLED');

        $configuration = new Config($config);
        $this->assertEquals('CALLED', $configuration->configureServiceManager($services->reveal()));

        return [
            'array'  => $expected,
            'config' => $configuration,
        ];
    }

    /**
     * @depends testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod
     */
    public function testToArrayReturnsConfiguration(array $dependencies): void
    {
        $configuration  = $dependencies['array'];
        $configInstance = $dependencies['config'];
        $this->assertSame($configuration, $configInstance->toArray());
    }
}
