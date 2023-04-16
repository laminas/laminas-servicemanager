<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompiler\AheadOfTimeFactoryCompiler;
use Laminas\ServiceManager\Tool\FactoryCreatorInterface;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler\TestAsset\WhateverEnum;
use LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler\TestAsset\WhateverTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

use const PHP_VERSION_ID;

final class AheadOfTimeFactoryCompilerTest extends TestCase
{
    private AheadOfTimeFactoryCompiler $compiler;

    /** @var FactoryCreatorInterface&MockObject */
    private FactoryCreatorInterface $factoryCreator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factoryCreator = $this->createMock(FactoryCreatorInterface::class);
        $this->compiler       = new AheadOfTimeFactoryCompiler(
            $this->factoryCreator,
        );
    }

    /**
     * @return array<non-empty-string, array{array}>
     */
    public function configurationsWithoutRegisteredServices(): array
    {
        return [
            'empty config'             => [
                [],
            ],
            'config with integer keys' => [
                [1, 2, 3],
            ],
            'config with container config without having registered services' => [
                ['service_manager' => ['factories' => []]],
            ],
            'config with non-array config parameters'                         => [
                ['foo' => 'bar'],
            ],
        ];
    }

    /**
     * @dataProvider configurationsWithoutRegisteredServices
     */
    public function testCanHandleConfigWithoutServicesRegisteredWithReflectionBasedAbstractFactory(array $config): void
    {
        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        self::assertSame([], $this->compiler->compile($config));
    }

    public function testCanHandleLaminasMvcServiceManagerConfiguration(): void
    {
        $config = [
            'service_manager' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::once())
            ->method('createFactory')
            ->with(stdClass::class)
            ->willReturn('created factory');

        $factories = $this->compiler->compile($config);
        self::assertCount(1, $factories);
        $factory = $factories[0];
        self::assertSame('service_manager', $factory->containerConfigurationKey);
        self::assertSame(stdClass::class, $factory->fullyQualifiedClassName);
        self::assertSame('created factory', $factory->generatedFactory);
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public function nonClassReferencingServiceNames(): array
    {
        return [
            'nonexistent-service-name' => [
                'foobar',
            ],
            'interface'                => [
                FactoryInterface::class,
            ],
            'trait'                    => [
                WhateverTrait::class,
            ],
        ];
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public function nonClassReferencingServiceNamesPhp81Upwards(): array
    {
        if (PHP_VERSION_ID < 80100) {
            return [];
        }

        return [
            'enum' => [
                WhateverEnum::class,
            ],
        ];
    }

    /**
     * @dataProvider nonClassReferencingServiceNames
     * @dataProvider nonClassReferencingServiceNamesPhp81Upwards
     */
    public function testWillRaiseExceptionWhenFactoryIsUsedWithNonClassReferencingService(string $serviceName): void
    {
        $config = [
            'dependencies' => [
                'factories' => [
                    $serviceName => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist or does not refer to an actual class');

        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        $this->compiler->compile($config);
    }

    public function testWillDetectSameServiceProvidedByMultipleServiceOrPluginManagers(): void
    {
        $config = [
            'foo' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
            'bar' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is registered in (at least) two service-/plugin-managers: foo, bar');

        $this->compiler->compile($config);
    }

    public function testWillProvideFactoriesForDifferentContainerConfigurations(): void
    {
        $config = [
            'foo' => [
                'factories' => [
                    ComplexDependencyObject::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
            'bar' => [
                'factories' => [
                    SimpleDependencyObject::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::exactly(2))
            ->method('createFactory')
            ->willReturnMap([
                [ComplexDependencyObject::class, [], 'factory for complex dependency object'],
                [SimpleDependencyObject::class, [], 'factory for simple dependency object'],
            ]);

        $factories = $this->compiler->compile($config);
        self::assertCount(2, $factories);
    }

    public function testWillDetectReflectionBasedFactoryInstancesWithClassString(): void
    {
        $config = [
            'foo' => [
                'factories' => [
                    ComplexDependencyObject::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
            'bar' => [
                'factories' => [
                    SimpleDependencyObject::class => new ReflectionBasedAbstractFactory(),
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::exactly(2))
            ->method('createFactory')
            ->willReturnMap([
                [ComplexDependencyObject::class, [], 'factory for complex dependency object'],
                [SimpleDependencyObject::class, [], 'factory for simple dependency object'],
            ]);

        $factories = $this->compiler->compile($config);
        self::assertCount(2, $factories);
    }

    public function testPassesAliasesToFactoryCreator(): void
    {
        $config = [
            'dependencies' => [
                'factories' => [
                    stdClass::class => new ReflectionBasedAbstractFactory([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::once())
            ->method('createFactory')
            ->with(stdClass::class, ['foo' => 'bar'])
            ->willReturn('generated factory');

        $factories = $this->compiler->compile($config);
        self::assertCount(1, $factories);
        self::assertSame('generated factory', $factories[0]->generatedFactory);
    }
}
