<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory;

use ArrayAccess;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function sprintf;

/**
 * @covers \Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory
 */
final class ReflectionBasedAbstractFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    private ReflectionBasedAbstractFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory   = new ReflectionBasedAbstractFactory();
    }

    public function nonClassRequestedNames(): array
    {
        return [
            'non-class-string' => ['non-class-string'],
        ];
    }

    /**
     * @dataProvider nonClassRequestedNames
     */
    public function testCanCreateReturnsFalseForNonClassRequestedNames(string $requestedName): void
    {
        self::assertFalse($this->factory->canCreate($this->container, $requestedName));
    }

    public function testCanCreateReturnsFalseWhenConstructorIsPrivate(): void
    {
        self::assertFalse(
            $this->factory->canCreate(
                $this->container,
                TestAsset\ClassWithPrivateConstructor::class
            ),
            'ReflectionBasedAbstractFactory should not be able to instantiate a class with a private constructor'
        );
    }

    public function testCanCreateReturnsTrueWhenClassHasNoConstructor(): void
    {
        self::assertTrue(
            $this->factory->canCreate(
                $this->container,
                TestAsset\ClassWithNoConstructor::class
            ),
            'ReflectionBasedAbstractFactory should be able to instantiate a class without a constructor'
        );
    }

    public function testFactoryInstantiatesClassDirectlyIfItHasNoConstructor(): void
    {
        $instance = $this->factory->__invoke($this->container, TestAsset\ClassWithNoConstructor::class);

        self::assertInstanceOf(TestAsset\ClassWithNoConstructor::class, $instance);
    }

    public function testFactoryInstantiatesClassDirectlyIfConstructorHasNoArguments(): void
    {
        $instance = $this->factory->__invoke($this->container, TestAsset\ClassWithEmptyConstructor::class);

        self::assertInstanceOf(TestAsset\ClassWithEmptyConstructor::class, $instance);
    }

    public function testFactoryRaisesExceptionWhenUnableToResolveATypeHintedService(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [TestAsset\SampleInterface::class, false],
            ])
            ->willReturn(false, false);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "sample" using type hint "%s"',
            TestAsset\ClassWithTypeHintedConstructorParameter::class,
            TestAsset\SampleInterface::class
        ));

        $this->factory->__invoke($this->container, TestAsset\ClassWithTypeHintedConstructorParameter::class);
    }

    public function testFactoryRaisesExceptionForScalarParameters(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "foo" to a class, interface, or array type',
            TestAsset\ClassWithScalarParameters::class
        ));

        $this->factory->__invoke($this->container, TestAsset\ClassWithScalarParameters::class);
    }

    public function testFactoryInjectsConfigServiceForConfigArgumentsTypeHintedAsArray(): void
    {
        $config = ['foo' => 'bar'];

        $this->container
            ->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(true);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $instance = $this->factory->__invoke($this->container, TestAsset\ClassAcceptingConfigToConstructor::class);

        self::assertInstanceOf(TestAsset\ClassAcceptingConfigToConstructor::class, $instance);
        self::assertSame($config, $instance->config);
    }

    public function testFactoryCanInjectKnownTypeHintedServices(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [TestAsset\SampleInterface::class, true],
            ]);

        $sample = $this->createMock(TestAsset\SampleInterface::class);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(TestAsset\SampleInterface::class)
            ->willReturn($sample);

        $instance = $this->factory->__invoke(
            $this->container,
            TestAsset\ClassWithTypeHintedConstructorParameter::class,
        );

        self::assertInstanceOf(TestAsset\ClassWithTypeHintedConstructorParameter::class, $instance);
        self::assertSame($sample, $instance->sample);
    }

    public function testFactoryResolvesTypeHintsForServicesToWellKnownServiceNames(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                ['ValidatorManager', true],
            ]);

        $validators = $this->createMock(TestAsset\ValidatorPluginManager::class);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('ValidatorManager')
            ->willReturn($validators);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory(
            $this->container,
            TestAsset\ClassAcceptingWellKnownServicesAsConstructorParameters::class
        );

        self::assertInstanceOf(
            TestAsset\ClassAcceptingWellKnownServicesAsConstructorParameters::class,
            $instance
        );
        self::assertSame($validators, $instance->validators);
    }

    public function testFactoryCanSupplyAMixOfParameterTypes(): void
    {
        $this->container
            ->expects(self::exactly(3))
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [TestAsset\SampleInterface::class, true],
                ['ValidatorManager', true],
            ]);

        $config     = ['foo' => 'bar'];
        $sample     = $this->createMock(TestAsset\SampleInterface::class);
        $validators = $this->createMock(TestAsset\ValidatorPluginManager::class);

        $this->container
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                [TestAsset\SampleInterface::class, $sample],
                ['ValidatorManager', $validators],
            ]);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory->__invoke($this->container, TestAsset\ClassWithMixedConstructorParameters::class);

        self::assertInstanceOf(TestAsset\ClassWithMixedConstructorParameters::class, $instance);
        self::assertSame($config, $instance->config);
        self::assertSame([], $instance->options);
        self::assertSame($sample, $instance->sample);
        self::assertSame($validators, $instance->validators);
    }

    public function testFactoryWillUseDefaultValueWhenPresentForScalarArgument(): void
    {
        $this->container
            ->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn(false);

        $instance = $this->factory->__invoke(
            $this->container,
            TestAsset\ClassWithScalarDependencyDefiningDefaultValue::class
        );

        self::assertInstanceOf(TestAsset\ClassWithScalarDependencyDefiningDefaultValue::class, $instance);
        self::assertSame('bar', $instance->foo);
    }

    /**
     * @see https://github.com/zendframework/zend-servicemanager/issues/239
     */
    public function testFactoryWillUseDefaultValueForTypeHintedArgument(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [ArrayAccess::class, false],
            ]);

        $instance = $this->factory->__invoke(
            $this->container,
            TestAsset\ClassWithTypehintedDefaultValue::class
        );

        self::assertInstanceOf(TestAsset\ClassWithTypehintedDefaultValue::class, $instance);
        self::assertNull($instance->value);
    }
}
