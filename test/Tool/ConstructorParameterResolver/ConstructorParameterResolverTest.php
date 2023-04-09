<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool\ConstructorParameterResolver;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\ConstructorParameterResolver\ConstructorParameterResolver;
use Laminas\ServiceManager\Tool\ConstructorParameterResolver\FallbackConstructorParameter;
use Laminas\ServiceManager\Tool\ConstructorParameterResolver\ServiceFromContainerConstructorParameter;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassAcceptingConfigToConstructor;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassAcceptingWellKnownServicesAsConstructorParameters;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithMixedConstructorParameters;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithNoConstructor;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithScalarDependencyDefiningDefaultValue;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithScalarParameters;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithTypeHintedConstructorParameter;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithTypehintedDefaultNullValue;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\SampleInterface;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ValidatorPluginManager;
use LaminasTest\ServiceManager\TestAsset\ClassDependingOnAnInterface;
use LaminasTest\ServiceManager\TestAsset\ClassWithConstructorWithOnlyOptionalArguments;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function sprintf;

final class ConstructorParameterResolverTest extends TestCase
{
    private ConstructorParameterResolver $resolver;

    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver  = new ConstructorParameterResolver();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testCanHandleClassNameWithoutConstructor(): void
    {
        $container  = $this->createMock(ContainerInterface::class);
        $parameters = $this->resolver->resolveConstructorParameterServiceNamesOrFallbackTypes(
            ClassWithNoConstructor::class,
            $container
        );
        self::assertSame([], $parameters);
    }

    public function testCanHandleClassNameWithOptionalConstructorDependencies(): void
    {
        $container                  = $this->createMock(ContainerInterface::class);
        $parameters                 = $this->resolver->resolveConstructorParameterServiceNamesOrFallbackTypes(
            ClassWithConstructorWithOnlyOptionalArguments::class,
            $container
        );
        $expectedResolvedParameters = [
            [],
            '',
            true,
            1,
            0.0,
            null,
        ];

        self::assertSameSize($expectedResolvedParameters, $parameters);
        foreach ($parameters as $index => $parameter) {
            self::assertInstanceOf(FallbackConstructorParameter::class, $parameter);
            $expectedParameter = $expectedResolvedParameters[$index] ?? null;
            self::assertSame($expectedParameter, $parameter->argumentValue);
        }
    }

    public function testWillDetectRequiredConstructorArguments(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [FactoryInterface::class, true],
            ]);

        $parameters = $this->resolver->resolveConstructorParameterServiceNamesOrFallbackTypes(
            ClassDependingOnAnInterface::class,
            $container
        );
        self::assertCount(1, $parameters);
        self::assertInstanceOf(ServiceFromContainerConstructorParameter::class, $parameters[0]);
        $parameter = $parameters[0];
        self::assertSame(FactoryInterface::class, $parameter->serviceName);
    }

    public function testRaisesExceptionWhenUnableToResolveATypeHintedService(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive(
                ['config'],
                [SampleInterface::class],
            )
            ->willReturn(false, false);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "sample" using type hint "%s"',
            ClassWithTypeHintedConstructorParameter::class,
            SampleInterface::class
        ));

        $this->resolver->resolveConstructorParameters(ClassWithTypeHintedConstructorParameter::class, $this->container);
    }

    public function testRaisesExceptionForScalarParameters(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Unable to create service "%s"; unable to resolve parameter "foo" to a class, interface, or array type',
            ClassWithScalarParameters::class
        ));

        $this->resolver->resolveConstructorParameters(ClassWithScalarParameters::class, $this->container);
    }

    public function testResolvesConfigServiceForConfigArgumentsTypeHintedAsArray(): void
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

        $parameters = $this->resolver->resolveConstructorParameters(
            ClassAcceptingConfigToConstructor::class,
            $this->container
        );
        self::assertCount(1, $parameters);
        self::assertSame($config, $parameters[0]);
    }

    public function testFactoryCanInjectKnownTypeHintedServices(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                [SampleInterface::class, true],
            ]);

        $sample = $this->createMock(SampleInterface::class);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(SampleInterface::class)
            ->willReturn($sample);

        $parameters = $this->resolver->resolveConstructorParameters(
            ClassWithTypeHintedConstructorParameter::class,
            $this->container,
        );

        self::assertCount(1, $parameters);
        self::assertSame($sample, $parameters[0]);
    }

    public function testResolvesTypeHintsForServicesToWellKnownServiceNames(): void
    {
        $this->container
            ->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                ['config', false],
                ['ValidatorManager', true],
            ]);

        $validators = $this->createMock(ValidatorPluginManager::class);

        $this->container
            ->expects(self::once())
            ->method('get')
            ->with('ValidatorManager')
            ->willReturn($validators);

        $parameters = $this->resolver->resolveConstructorParameters(
            ClassAcceptingWellKnownServicesAsConstructorParameters::class,
            $this->container,
            [ValidatorPluginManager::class => 'ValidatorManager'],
        );

        self::assertCount(1, $parameters);
        self::assertSame($validators, $parameters[0]);
    }

    /**
     * @depends testWillResolveConstructorArgumentsAccordingToTheirPosition
     */
    public function testResolvesAMixOfParameterTypes(): void
    {
        $this->container
            ->expects(self::exactly(3))
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [SampleInterface::class, true],
                ['ValidatorManager', true],
            ]);

        $config     = ['foo' => 'bar'];
        $sample     = $this->createMock(SampleInterface::class);
        $validators = $this->createMock(ValidatorPluginManager::class);

        $this->container
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                [SampleInterface::class,  $sample],
                ['ValidatorManager', $validators],
            ]);

        $parameters = $this->resolver->resolveConstructorParameters(
            ClassWithMixedConstructorParameters::class,
            $this->container,
            [ValidatorPluginManager::class => 'ValidatorManager']
        );

        self::assertCount(4, $parameters);
        self::assertSame($config, $parameters[0]);
        self::assertSame($sample, $parameters[1]);
        self::assertSame($validators, $parameters[2]);
        self::assertNull($parameters[3], 'Optional parameters should resolve to their default value.');
    }

    public function testResolvesDefaultValuesWhenPresentForScalarArgument(): void
    {
        $parameters = $this->resolver->resolveConstructorParameters(
            ClassWithScalarDependencyDefiningDefaultValue::class,
            $this->container,
        );

        self::assertCount(1, $parameters);
        self::assertSame('bar', $parameters[0]);
    }

    /**
     * @see https://github.com/zendframework/zend-servicemanager/issues/239
     */
    public function testWillResolveToDefaultValueForTypeHintedArgumentWhichDoesNotExistInContainer(): void
    {
        $parameters = $this->resolver->resolveConstructorParameters(
            ClassWithTypehintedDefaultNullValue::class,
            $this->container,
        );

        self::assertCount(1, $parameters);
        self::assertNull($parameters[0]);
    }

    public function testWillResolveConstructorArgumentsAccordingToTheirPosition(): void
    {
        $this->container
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [SampleInterface::class, true],
                [ValidatorPluginManager::class, true],
            ]);

        $sample     = $this->createMock(SampleInterface::class);
        $validators = $this->createMock(ValidatorPluginManager::class);

        $this->container
            ->method('get')
            ->willReturnMap([
                ['config', ['foo' => 'bar']],
                [SampleInterface::class,  $sample],
                [ValidatorPluginManager::class, $validators],
            ]);

        $parameters = $this->resolver->resolveConstructorParameters(
            ClassWithMixedConstructorParameters::class,
            $this->container
        );

        self::assertCount(4, $parameters);
        self::assertSame(['foo' => 'bar'], $parameters[0]);
        self::assertSame($sample, $parameters[1]);
        self::assertSame($validators, $parameters[2]);
        self::assertNull($parameters[3], 'Optional parameters should resolve to their default value.');
    }
}
