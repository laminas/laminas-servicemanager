<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\ExceptionInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Tool\ConstructorParameterResolver\ConstructorParameterResolverInterface;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ClassWithConstructorAcceptingAnyArgument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @covers \Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory
 */
final class ReflectionBasedAbstractFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    private ReflectionBasedAbstractFactory $factory;

    /** @var ConstructorParameterResolverInterface&MockObject */
    private ConstructorParameterResolverInterface $constructorParameterResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container                    = $this->createMock(ContainerInterface::class);
        $this->constructorParameterResolver = $this->createMock(ConstructorParameterResolverInterface::class);
        $this->factory                      = new ReflectionBasedAbstractFactory(
            [],
            $this->constructorParameterResolver
        );
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public static function invalidRequestNames(): array
    {
        return [
            'empty-string'                   => [''],
            'non-existing-class'             => ['non-class-string'],
            'class-with-private-constructor' => [TestAsset\ClassWithPrivateConstructor::class],
        ];
    }

    /**
     * @dataProvider invalidRequestNames
     */
    public function testCanCreateReturnsFalseForUnsupportedRequestNames(string $requestedName): void
    {
        self::assertFalse($this->factory->canCreate($this->container, $requestedName));
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

    /**
     * @return array<non-empty-string,array{class-string}>
     */
    public static function classNamesWithoutConstructorArguments(): array
    {
        return [
            'no-constructor'           => [
                TestAsset\ClassWithNoConstructor::class,
            ],
            'no-constructor-arguments' => [
                TestAsset\ClassWithEmptyConstructor::class,
            ],
        ];
    }

    /**
     * @param class-string $className
     * @dataProvider classNamesWithoutConstructorArguments
     */
    public function testFactoryInstantiatesClassWithoutConstructorArguments(string $className): void
    {
        $instance = $this->factory->__invoke($this->container, $className);

        self::assertInstanceOf($className, $instance);
    }

    public function testWillThrowInvalidArgumentExceptionForInExistentClassName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('can only be used with class names.');
        $this->factory->__invoke($this->container, 'serviceName');
    }

    public function testFactoryPassesContainerExceptions(): void
    {
        $this->expectException(ExceptionInterface::class);
        $this->constructorParameterResolver
            ->method('resolveConstructorParameters')
            ->with(stdClass::class)
            ->willThrowException($this->createMock(ExceptionInterface::class));

        $this->factory->__invoke($this->container, stdClass::class);
    }

    public function testFactoryPassesAliasesToArgumentResolver(): void
    {
        $factory = new ReflectionBasedAbstractFactory([
            'Foo' => 'Bar',
        ], $this->constructorParameterResolver);

        $this->constructorParameterResolver
            ->expects(self::once())
            ->method('resolveConstructorParameters')
            ->with(stdClass::class, $this->container, ['Foo' => 'Bar']);

        $factory->__invoke($this->container, stdClass::class);
    }

    public function testPassesConstructorArgumentsInTheSameOrderAsReturnedFromResolver(): void
    {
        $resolvedParameters = ['foo', true, 1, 0.0, static fn (): bool => true];

        $this->constructorParameterResolver
            ->expects(self::once())
            ->method('resolveConstructorParameters')
            ->willReturn($resolvedParameters);

        $factory  = new ReflectionBasedAbstractFactory([], $this->constructorParameterResolver);
        $instance = $factory->__invoke($this->container, ClassWithConstructorAcceptingAnyArgument::class);
        self::assertInstanceOf(ClassWithConstructorAcceptingAnyArgument::class, $instance);
        foreach ($resolvedParameters as $index => $parameter) {
            self::assertArrayHasKey($index, $instance->arguments);
            self::assertSame($parameter, $instance->arguments[$index]);
        }
    }
}
