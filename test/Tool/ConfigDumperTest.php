<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use interop\container\containerinterface;
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

use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

/**
 * @covers \Laminas\ServiceManager\Tool\ConfigDumper
 */
final class ConfigDumperTest extends TestCase
{
    private ConfigDumper $dumper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dumper = new ConfigDumper();
    }

    public function testCreateDependencyConfigExceptsIfClassNameIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be a string, integer given');

        /** @psalm-suppress InvalidArgument */
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

        self::assertSame(
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

        self::assertSame(
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class        => [],
                    SimpleDependencyObject::class => [
                        InvokableObject::class,
                    ],
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

        self::assertSame($expectedConfig, $config);
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

        $container = $this->createMock(containerinterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with(ObjectWithScalarDependency::class)
            ->willReturn(false);

        $dumper = new ConfigDumper($container);

        $dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithScalarDependency::class
        );
    }

    public function testCreateDependencyConfigWithContainerWithoutTypeHintedParameter(): void
    {
        $container = $this->createMock(containerinterface::class);
        $container
            ->expects(self::once())
            ->method('has')
            ->with(ObjectWithScalarDependency::class)
            ->willReturn(true);

        $dumper = new ConfigDumper($container);

        $config = $dumper->createDependencyConfig(
            [ConfigAbstractFactory::class => []],
            ObjectWithObjectScalarDependency::class
        );

        self::assertSame(
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class                  => [],
                    SimpleDependencyObject::class           => [
                        InvokableObject::class,
                    ],
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

        self::assertSame(
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class                  => [],
                    SimpleDependencyObject::class           => [
                        InvokableObject::class,
                    ],
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

        self::assertSame($config, $this->dumper->createDependencyConfig($config, SimpleDependencyObject::class));
    }

    public function testCreateDependencyConfigWorksWithMultipleDependenciesOfSameType(): void
    {
        $expectedConfig = [
            ConfigAbstractFactory::class => [
                InvokableObject::class        => [],
                DoubleDependencyObject::class => [
                    InvokableObject::class,
                    InvokableObject::class,
                ],
            ],
        ];

        self::assertSame($expectedConfig, $this->dumper->createDependencyConfig([], DoubleDependencyObject::class));
    }

    public function testCreateFactoryMappingsExceptsIfClassNameIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be a string, integer given');

        /** @psalm-suppress InvalidArgument */
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

        self::assertSame($config, $this->dumper->createFactoryMappings($config, InvokableObject::class));
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

        self::assertSame($expectedConfig, $this->dumper->createFactoryMappings([], InvokableObject::class));
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

        self::assertSame($config, $this->dumper->createFactoryMappings($config, InvokableObject::class));
    }

    public function testCreateFactoryMappingsFromConfigReturnsIfNoConfigKey(): void
    {
        self::assertSame([], $this->dumper->createFactoryMappingsFromConfig([]));
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

        self::assertSame($expectedConfig, $this->dumper->createFactoryMappingsFromConfig($config));
    }

    /**
     * @depends testCreateDependencyConfigSimpleDependencyReturnsCorrectly
     */
    public function testDumpConfigFileReturnsContentsForConfigFileUsingUsingClassNotationAndShortArrays(
        array $config
    ): void {
        $formatted = $this->dumper->dumpConfigFile($config);
        self::assertStringContainsString(
            '<' . "?php\n\n/**\n * This file generated by Laminas\ServiceManager\Tool\ConfigDumper.\n",
            $formatted
        );

        self::assertStringNotContainsString('array(', $formatted);
        self::assertStringContainsString('::class', $formatted);

        $file = tempnam(sys_get_temp_dir(), 'ZSCLI');
        file_put_contents($file, $formatted);
        $test = include $file;
        unlink($file);

        self::assertSame($test, $config);
    }

    public function testWillDumpConfigForClassDependingOnInterfaceButOmitInterfaceConfig(): void
    {
        $config = $this->dumper->createDependencyConfig([], ClassDependingOnAnInterface::class);

        self::assertSame(
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
