<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\ConfigDumper;
use LaminasTest\ServiceManager\TestAsset\ClassDependingOnAnInterface;
use LaminasTest\ServiceManager\TestAsset\DoubleDependencyObject;
use LaminasTest\ServiceManager\TestAsset\FailingFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\ObjectWithObjectScalarDependency;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SecondComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class ConfigDumperTest extends TestCase
{
    use ProphecyTrait;

    private \Laminas\ServiceManager\Tool\ConfigDumper $dumper;

    public function setUp(): void
    {
        $this->dumper = new ConfigDumper();
    }

    public function testCreateDependencyConfigExceptsIfClassNameIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be a string, integer given');
        $this->dumper->createDependencyConfig([], 42);
    }

    public function testCreateDependencyConfigExceptsIfClassDoesNotExist(): void
    {
        $className = 'Dirk\Gentley\Holistic\Detective\Agency';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find class or interface with name ' . $className);
        $this->dumper->createDependencyConfig([], $className);
    }

    public function testCreateDependencyConfigInvokableObjectReturnsEmptyArray(): void
    {
        $config = $this->dumper->createDependencyConfig([], InvokableObject::class);
        $this->assertEquals(
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ],
            $config
        );
    }

    public function testCreateDependencyConfigSimpleDependencyReturnsCorrectly(): array
    {
        $config = $this->dumper->createDependencyConfig([], SimpleDependencyObject::class);
        $this->assertEquals(
            [
                ConfigAbstractFactory::class => [
                    SimpleDependencyObject::class => [
                        InvokableObject::class,
                    ],
                    InvokableObject::class        => [],
                ],
            ],
            $config
        );
        return $config;
    }

    public function testCreateDependencyConfigClassWithoutConstructorHandlesAsInvokable(): void
    {
        $expectedConfig = [
            ConfigAbstractFactory::class => [
                FailingFactory::class => [],
            ],
        ];
        $config         = $this->dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            FailingFactory::class
        );
        $this->assertEquals($expectedConfig, $config);
    }

    public function testCreateDependencyConfigWithoutTypeHintedParameterExcepts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot create config for constructor argument "aName", '
                . 'it has no type hint, or non-class/interface type hint'
        );
        $this->dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithScalarDependency::class
        );
    }

    public function testCreateDependencyConfigWithContainerAndNoServiceWithoutTypeHintedParameterExcepts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot create config for constructor argument "aName", '
                . 'it has no type hint, or non-class/interface type hint'
        );
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ObjectWithScalarDependency::class)
            ->shouldBeCalled()
            ->willReturn(false);

        $dumper = new ConfigDumper($container->reveal());

        $dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithScalarDependency::class
        );
    }

    public function testCreateDependencyConfigWithContainerWithoutTypeHintedParameter(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(ObjectWithScalarDependency::class)
            ->shouldBeCalled()
            ->willReturn(true);

        $dumper = new ConfigDumper($container->reveal());

        $config = $dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithObjectScalarDependency::class
        );

        $this->assertEquals(
            [
                ConfigAbstractFactory::class => [
                    SimpleDependencyObject::class           => [
                        InvokableObject::class,
                    ],
                    InvokableObject::class                  => [],
                    ObjectWithObjectScalarDependency::class => [
                        SimpleDependencyObject::class,
                        ObjectWithScalarDependency::class,
                    ],
                ],
            ],
            $config
        );
    }

    public function testCreateDependencyConfigWithoutTypeHintedParameterIgnoringUnresolved(): void
    {
        $config = $this->dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithObjectScalarDependency::class,
            true
        );
        $this->assertEquals(
            [
                ConfigAbstractFactory::class => [
                    SimpleDependencyObject::class           => [
                        InvokableObject::class,
                    ],
                    InvokableObject::class                  => [],
                    ObjectWithObjectScalarDependency::class => [
                        SimpleDependencyObject::class,
                        ObjectWithScalarDependency::class,
                    ],
                ],
            ],
            $config
        );
    }

    public function testCreateDependencyConfigWorksWithExistingConfig(): void
    {
        $config = [
            ConfigAbstractFactory::class => [
                InvokableObject::class        => [],
                SimpleDependencyObject::class => [
                    InvokableObject::class,
                ],
            ],
        ];

        $this->assertEquals($config, $this->dumper->createDependencyConfig($config, SimpleDependencyObject::class));
    }

    public function testCreateDependencyConfigWorksWithMultipleDependenciesOfSameType(): void
    {
        $expectedConfig = [
            ConfigAbstractFactory::class => [
                DoubleDependencyObject::class => [
                    InvokableObject::class,
                    InvokableObject::class,
                ],
                InvokableObject::class        => [],
            ],
        ];

        $this->assertEquals($expectedConfig, $this->dumper->createDependencyConfig([], DoubleDependencyObject::class));
    }

    public function testCreateFactoryMappingsExceptsIfClassNameIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be a string, integer given');
        $this->dumper->createFactoryMappings([], 42);
    }

    public function testCreateFactoryMappingsExceptsIfClassDoesNotExist(): void
    {
        $className = 'Dirk\Gentley\Holistic\Detective\Agency';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find class or interface with name ' . $className);
        $this->dumper->createFactoryMappings([], $className);
    }

    public function testCreateFactoryMappingsReturnsUnmodifiedArrayIfMappingExists(): void
    {
        $config = [
            'service_manager' => [
                'factories' => [
                    InvokableObject::class => ConfigAbstractFactory::class,
                ],
            ],
        ];
        $this->assertEquals($config, $this->dumper->createFactoryMappings($config, InvokableObject::class));
    }

    public function testCreateFactoryMappingsAddsClassIfNotExists(): void
    {
        $expectedConfig = [
            'service_manager' => [
                'factories' => [
                    InvokableObject::class => ConfigAbstractFactory::class,
                ],
            ],
        ];
        $this->assertEquals($expectedConfig, $this->dumper->createFactoryMappings([], InvokableObject::class));
    }

    public function testCreateFactoryMappingsIgnoresExistingsMappings(): void
    {
        $config = [
            'service_manager' => [
                'factories' => [
                    InvokableObject::class => 'SomeOtherExistingFactory',
                ],
            ],
        ];
        $this->assertEquals($config, $this->dumper->createFactoryMappings($config, InvokableObject::class));
    }

    public function testCreateFactoryMappingsFromConfigReturnsIfNoConfigKey(): void
    {
        $this->assertEquals([], $this->dumper->createFactoryMappingsFromConfig([]));
    }

    public function testCreateFactoryMappingsFromConfigExceptsWhenConfigNotArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Config key for ' . ConfigAbstractFactory::class . ' should be an array, boolean given'
        );

        $this->dumper->createFactoryMappingsFromConfig(
            [
                ConfigAbstractFactory::class => true,
            ]
        );
    }

    public function testCreateFactoryMappingsFromConfigWithWorkingConfig(): void
    {
        $config = [
            ConfigAbstractFactory::class => [
                InvokableObject::class               => [],
                SimpleDependencyObject::class        => [
                    InvokableObject::class,
                ],
                SecondComplexDependencyObject::class => [
                    InvokableObject::class,
                ],
            ],
        ];

        $expectedConfig = [
            ConfigAbstractFactory::class => [
                InvokableObject::class               => [],
                SimpleDependencyObject::class        => [
                    InvokableObject::class,
                ],
                SecondComplexDependencyObject::class => [
                    InvokableObject::class,
                ],
            ],
            'service_manager'            => [
                'factories' => [
                    InvokableObject::class               => ConfigAbstractFactory::class,
                    SimpleDependencyObject::class        => ConfigAbstractFactory::class,
                    SecondComplexDependencyObject::class => ConfigAbstractFactory::class,
                ],
            ],
        ];

        $this->assertEquals($expectedConfig, $this->dumper->createFactoryMappingsFromConfig($config));
    }

    /**
     * @depends testCreateDependencyConfigSimpleDependencyReturnsCorrectly
     */
    public function testDumpConfigFileReturnsContentsForConfigFileUsingUsingClassNotationAndShortArrays(
        array $config
    ): void {
        $formatted = $this->dumper->dumpConfigFile($config);
        $this->assertStringContainsString(
            '<' . "?php\n\n/**\n * This file generated by Laminas\ServiceManager\Tool\ConfigDumper.\n",
            $formatted
        );

        $this->assertStringNotContainsString('array(', $formatted);
        $this->assertStringContainsString('::class', $formatted);

        $file = tempnam(sys_get_temp_dir(), 'ZSCLI');
        file_put_contents($file, $formatted);
        $test = include $file;
        unlink($file);

        $this->assertEquals($test, $config);
    }

    public function testWillDumpConfigForClassDependingOnInterfaceButOmitInterfaceConfig(): void
    {
        $config = $this->dumper->createDependencyConfig([], ClassDependingOnAnInterface::class);
        $this->assertEquals(
            [
                ConfigAbstractFactory::class => [
                    ClassDependingOnAnInterface::class => [
                        FactoryInterface::class,
                    ],
                ],
            ],
            $config
        );
    }
}
