<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory;

use ArrayAccess;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function sprintf;

class ReflectionBasedAbstractFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
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
        $this->assertFalse($factory->canCreate($this->container->reveal(), $requestedName));
    }

    public function testCanCreateReturnsFalseWhenConstructorIsPrivate(): void
    {
        $this->assertFalse(
            (new ReflectionBasedAbstractFactory())->canCreate(
                $this->container->reveal(),
                TestAsset\ClassWithPrivateConstructor::class
            ),
            'ReflectionBasedAbstractFactory should not be able to instantiate a class with a private constructor'
        );
    }

    public function testCanCreateReturnsTrueWhenClassHasNoConstructor(): void
    {
        $this->assertTrue(
            (new ReflectionBasedAbstractFactory())->canCreate(
                $this->container->reveal(),
                TestAsset\ClassWithNoConstructor::class
            ),
            'ReflectionBasedAbstractFactory should be able to instantiate a class without a constructor'
        );
    }

    public function testFactoryInstantiatesClassDirectlyIfItHasNoConstructor(): void
    {
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container->reveal(), TestAsset\ClassWithNoConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassWithNoConstructor::class, $instance);
    }

    public function testFactoryInstantiatesClassDirectlyIfConstructorHasNoArguments(): void
    {
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container->reveal(), TestAsset\ClassWithEmptyConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassWithEmptyConstructor::class, $instance);
    }

    public function testFactoryRaisesExceptionWhenUnableToResolveATypeHintedService(): void
    {
        $this->container->has(TestAsset\SampleInterface::class)->willReturn(false);
        $this->container->has('config')->willReturn(false);
        $factory = new ReflectionBasedAbstractFactory();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "sample" using type hint "%s"',
            TestAsset\ClassWithTypeHintedConstructorParameter::class,
            TestAsset\SampleInterface::class
        ));
        $factory($this->container->reveal(), TestAsset\ClassWithTypeHintedConstructorParameter::class);
    }

    public function testFactoryRaisesExceptionForScalarParameters(): void
    {
        $factory = new ReflectionBasedAbstractFactory();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "foo" to a class, interface, or array type',
            TestAsset\ClassWithScalarParameters::class
        ));
        $factory($this->container->reveal(), TestAsset\ClassWithScalarParameters::class);
    }

    public function testFactoryInjectsConfigServiceForConfigArgumentsTypeHintedAsArray(): void
    {
        $config = ['foo' => 'bar'];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container->reveal(), TestAsset\ClassAcceptingConfigToConstructor::class);
        $this->assertInstanceOf(TestAsset\ClassAcceptingConfigToConstructor::class, $instance);
        $this->assertEquals($config, $instance->config);
    }

    public function testFactoryCanInjectKnownTypeHintedServices(): void
    {
        $sample = $this->prophesize(TestAsset\SampleInterface::class)->reveal();
        $this->container->has('config')->willReturn(false);
        $this->container->has(TestAsset\SampleInterface::class)->willReturn(true);
        $this->container->get(TestAsset\SampleInterface::class)->willReturn($sample);

        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory($this->container->reveal(), TestAsset\ClassWithTypeHintedConstructorParameter::class);
        $this->assertInstanceOf(TestAsset\ClassWithTypeHintedConstructorParameter::class, $instance);
        $this->assertSame($sample, $instance->sample);
    }

    public function testFactoryResolvesTypeHintsForServicesToWellKnownServiceNames(): void
    {
        $this->container->has('config')->willReturn(false);

        $validators = $this->prophesize(TestAsset\ValidatorPluginManager::class)->reveal();
        $this->container->has('ValidatorManager')->willReturn(true);
        $this->container->get('ValidatorManager')->willReturn($validators);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory(
            $this->container->reveal(),
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
        $validators = $this->prophesize(TestAsset\ValidatorPluginManager::class)->reveal();
        $this->container->has('ValidatorManager')->willReturn(true);
        $this->container->get('ValidatorManager')->willReturn($validators);

        $sample = $this->prophesize(TestAsset\SampleInterface::class)->reveal();
        $this->container->has(TestAsset\SampleInterface::class)->willReturn(true);
        $this->container->get(TestAsset\SampleInterface::class)->willReturn($sample);

        $config = ['foo' => 'bar'];
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);

        $factory  = new ReflectionBasedAbstractFactory([TestAsset\ValidatorPluginManager::class => 'ValidatorManager']);
        $instance = $factory($this->container->reveal(), TestAsset\ClassWithMixedConstructorParameters::class);
        $this->assertInstanceOf(TestAsset\ClassWithMixedConstructorParameters::class, $instance);

        $this->assertEquals($config, $instance->config);
        $this->assertEquals([], $instance->options);
        $this->assertSame($sample, $instance->sample);
        $this->assertSame($validators, $instance->validators);
    }

    public function testFactoryWillUseDefaultValueWhenPresentForScalarArgument(): void
    {
        $this->container->has('config')->willReturn(false);
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory(
            $this->container->reveal(),
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
        $this->container->has('config')->willReturn(false);
        $this->container->has(ArrayAccess::class)->willReturn(false);
        $factory  = new ReflectionBasedAbstractFactory();
        $instance = $factory(
            $this->container->reveal(),
            TestAsset\ClassWithTypehintedDefaultValue::class
        );
        $this->assertInstanceOf(TestAsset\ClassWithTypehintedDefaultValue::class, $instance);
        $this->assertNull($instance->value);
    }
}
