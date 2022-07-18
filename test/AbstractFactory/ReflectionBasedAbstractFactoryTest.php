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

class ReflectionBasedAbstractFactoryTest extends TestCase
{
    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
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
        $factory = new ReflectionBasedAbstractFactory();
        $this->assertFalse($factory->canCreate($this->container, $requestedName));
    }

    public function testCanCreateReturnsFalseWhenConstructorIsPrivate(): void
    {
        $this->assertFalse(
            (new ReflectionBasedAbstractFactory())->canCreate(
                $this->container,
                TestAsset\ClassWithPrivateConstructor::class
            ),
            'ReflectionBasedAbstractFactory should not be able to instantiate a class with a private constructor'
        );
    }

    public function testCanCreateReturnsTrueWhenClassHasNoConstructor(): void
    {
        $this->assertTrue(
            (new ReflectionBasedAbstractFactory())->canCreate(
                $this->container,
                TestAsset\ClassWithNoConstructor::class
            ),
            'ReflectionBasedAbstractFactory should be able to instantiate a class without a constructor'
        );
    }

    public function testFactoryInstantiatesClassDirectlyIfItHasNoConstructor(): void
    {
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container, TestAsset\ClassWithNoConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassWithNoConstructor::class, $instance);
    }

    public function testFactoryInstantiatesClassDirectlyIfConstructorHasNoArguments(): void
    {
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container, TestAsset\ClassWithEmptyConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassWithEmptyConstructor::class, $instance);
    }

    public function testFactoryRaisesExceptionWhenUnableToResolveATypeHintedService(): void
    {
        $this->container
            ->method('has')
            ->withConsecutive(['config'], [TestAsset\SampleInterface::class])
            ->willReturn(false);

        $factory = new ReflectionBasedAbstractFactory();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "sample" using type hint "%s"',
            TestAsset\ClassWithTypeHintedConstructorParameter::class,
            TestAsset\SampleInterface::class
        ));
        $factory($this->container, TestAsset\ClassWithTypeHintedConstructorParameter::class);
    }

    public function testFactoryRaisesExceptionForScalarParameters(): void
    {
        $factory = new ReflectionBasedAbstractFactory();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "foo" to a class, interface, or array type',
            TestAsset\ClassWithScalarParameters::class
        ));
        $factory($this->container, TestAsset\ClassWithScalarParameters::class);
    }

    public function testFactoryInjectsConfigServiceForConfigArgumentsTypeHintedAsArray(): void
    {
        $config = ['foo' => 'bar'];
        $this->container->method('has')->with('config')->willReturn(true);
        $this->container->method('get')->with('config')->willReturn($config);

        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container, TestAsset\ClassAcceptingConfigToConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassAcceptingConfigToConstructor::class, $instance);
        $this->assertEquals($config, $instance->config);
    }

    public function testFactoryCanInjectKnownTypeHintedServices(): void
    {
        $sample = $this->createStub(TestAsset\SampleInterface::class);
        $this->container
            ->method('has')
            ->withConsecutive(['config'], [TestAsset\SampleInterface::class])
            ->willReturnOnConsecutiveCalls(false, true);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(TestAsset\SampleInterface::class)
            ->willReturn($sample);

        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container, TestAsset\ClassWithTypeHintedConstructorParameter::class);
        $this->assertInstanceOf(TestAsset\ClassWithTypeHintedConstructorParameter::class, $instance);
        $this->assertSame($sample, $instance->sample);
    }

    public function testFactoryResolvesTypeHintsForServicesToWellKnownServiceNames(): void
    {
        $this->container
            ->method('has')
            ->withConsecutive(['config'], ['ValidatorManager'])
            ->willReturnOnConsecutiveCalls(false, true);

        $validators = $this->createStub(TestAsset\ValidatorPluginManager::class);
        $this->container
            ->method('get')
            ->with('ValidatorManager')
            ->willReturn($validators);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory(
            $this->container,
            TestAsset\ClassAcceptingWellKnownServicesAsConstructorParameters::class
        );
        $this->assertInstanceOf(
            TestAsset\ClassAcceptingWellKnownServicesAsConstructorParameters::class,
            $instance
        );
        $this->assertSame($validators, $instance->validators);
    }

    public function testFactoryCanSupplyAMixOfParameterTypes(): void
    {
        $validators = $this->createStub(TestAsset\ValidatorPluginManager::class);
        $this->container
            ->method('has')
            ->withConsecutive(['config'], [TestAsset\SampleInterface::class], ['ValidatorManager'])
            ->willReturn(true);

        $sample = $this->createStub(TestAsset\SampleInterface::class);
        $config = ['foo' => 'bar'];

        $this->container
            ->method('get')
            ->withConsecutive([TestAsset\SampleInterface::class], ['ValidatorManager'], ['config'])
            ->willReturnOnConsecutiveCalls($sample, $validators, $config);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory($this->container, TestAsset\ClassWithMixedConstructorParameters::class);
        $this->assertInstanceOf(TestAsset\ClassWithMixedConstructorParameters::class, $instance);

        $this->assertEquals($config, $instance->config);
        $this->assertEquals([], $instance->options);
        $this->assertSame($sample, $instance->sample);
        $this->assertSame($validators, $instance->validators);
    }

    public function testFactoryWillUseDefaultValueWhenPresentForScalarArgument(): void
    {
        $this->container->method('has')->with('config')->willReturn(false);
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory(
            $this->container,
            TestAsset\ClassWithScalarDependencyDefiningDefaultValue::class
        );
        $this->assertInstanceOf(TestAsset\ClassWithScalarDependencyDefiningDefaultValue::class, $instance);
        $this->assertEquals('bar', $instance->foo);
    }

    /**
     * @see https://github.com/zendframework/zend-servicemanager/issues/239
     */
    public function testFactoryWillUseDefaultValueForTypeHintedArgument(): void
    {
        $this->container
            ->method('has')
            ->withConsecutive(['config'], [ArrayAccess::class])
            ->willReturn(false);
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory(
            $this->container,
            TestAsset\ClassWithTypehintedDefaultValue::class
        );
        $this->assertInstanceOf(TestAsset\ClassWithTypehintedDefaultValue::class, $instance);
        $this->assertNull($instance->value);
    }
}
