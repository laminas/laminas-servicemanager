<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

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

    public function testMergeArrays()
    {
        $config = [
            'invokables' => [
                'foo' => TestAsset\InvokableObject::class,
            ],
            'delegators' => [
                'foo' => [
                    TestAsset\PreDelegator::class,
                ]
            ],
            'factories' => [
                'service' => TestAsset\FactoryObject::class,
            ],
        ];

        $configuration = new TestAsset\ExtendedConfig($config);
        $result = $configuration->toArray();

        $expected = [
            'invokables' => [
                'foo' => TestAsset\InvokableObject::class,
                TestAsset\InvokableObject::class => TestAsset\InvokableObject::class,
            ],
            'delegators' => [
                'foo' => [
                    TestAsset\InvokableObject::class,
                    TestAsset\PreDelegator::class,
                ],
            ],
            'factories' => [
                'service' => TestAsset\FactoryObject::class,
            ],
        ];

        self::assertEquals($expected, $result);
    }

    public function testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod()
    {
        $expected = [
            'abstract_factories' => [
                __CLASS__,
                __NAMESPACE__,
            ],
            'aliases' => [
                'foo' => __CLASS__,
                'bar' => __NAMESPACE__,
            ],
            'delegators' => [
                'foo' => [
                    __CLASS__,
                    __NAMESPACE__,
                ]
            ],
            'factories' => [
                'foo' => __CLASS__,
                'bar' => __NAMESPACE__,
            ],
            'initializers' => [
                __CLASS__,
                __NAMESPACE__,
            ],
            'invokables' => [
                'foo' => __CLASS__,
                'bar' => __NAMESPACE__,
            ],
            'lazy_services' => [
                'class_map' => [
                    __CLASS__     => __CLASS__,
                    __NAMESPACE__ => __NAMESPACE__,
                ],
            ],
            'services' => [
                'foo' => $this,
            ],
            'shared' => [
                __CLASS__     => true,
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
        self::assertEquals('CALLED', $configuration->configureServiceManager($services->reveal()));

        return [
            'array'  => $expected,
            'config' => $configuration,
        ];
    }

    /**
     * @depends testPassesKnownServiceConfigKeysToServiceManagerWithConfigMethod
     */
    public function testToArrayReturnsConfiguration($dependencies)
    {
        $configuration  = $dependencies['array'];
        $configInstance = $dependencies['config'];
        self::assertSame($configuration, $configInstance->toArray());
    }
}
