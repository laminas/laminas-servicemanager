<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Proxy;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use LaminasTest\ServiceManager\TestAsset\ClassWithCallbackMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

#[CoversClass(LazyServiceFactory::class)]
final class LazyServiceFactoryTest extends TestCase
{
    private LazyServiceFactory $factory;

    /** @var LazyLoadingValueHolderFactory&MockObject */
    private LazyLoadingValueHolderFactory $proxyFactory;

    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    /** {@inheritDoc} */
    protected function setUp(): void
    {
        parent::setUp();

        $this->proxyFactory = $this->createMock(LazyLoadingValueHolderFactory::class);
        $this->container    = $this->createMock(ContainerInterface::class);

        $servicesMap = [
            'fooService' => 'FooClass',
        ];

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->factory = new LazyServiceFactory($this->proxyFactory, $servicesMap);
    }

    public function testImplementsDelegatorFactoryInterface(): void
    {
        self::assertInstanceOf(DelegatorFactoryInterface::class, $this->factory);
    }

    public function testThrowExceptionWhenServiceNotExists(): void
    {
        $callback = $this->createMock(ClassWithCallbackMethod::class);

        $callback
            ->expects(self::never())
            ->method('callback');

        $this->proxyFactory
            ->expects($this->never())
            ->method('createProxy');

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('The requested service "not_exists" was not found in the provided services map');

        $this->factory->__invoke($this->container, 'not_exists', [$callback, 'callback']);
    }

    public function testCreates(): void
    {
        $callback = new ClassWithCallbackMethod('fooValue');

        $expectedService = $this->createMock(VirtualProxyInterface::class);
        $proxy           = $this->createMock(LazyLoadingInterface::class);

        $this->proxyFactory
            ->expects(self::once())
            ->method('createProxy')
            ->willReturnCallback(
                static function ($className, $initializer) use ($expectedService, $proxy): MockObject {
                    self::assertEquals('FooClass', $className, 'class name not match');

                    $wrappedInstance = null;
                    $result          = $initializer($wrappedInstance, $proxy);

                    self::assertEquals('fooValue', $wrappedInstance, 'expected callback return value');
                    self::assertTrue($result, 'initializer should return true');

                    return $expectedService;
                }
            );

        $result = $this->factory->__invoke($this->container, 'fooService', $callback->callback(...));

        self::assertSame($expectedService, $result, 'service created not match the expected');
    }

    public function testHandlesExceptionInInitializer(): void
    {
        $callback = function (): void {
            throw new RuntimeException('Test exception');
        };

        $proxy       = $this->createMock(LazyLoadingInterface::class);
        $initializer = static fn(): bool => true;

        $proxy->expects(self::once())
            ->method('getProxyInitializer')
            ->willReturn($initializer);

        $proxy
            ->expects(self::exactly(2))
            ->method('setProxyInitializer')
            ->willReturnMap(
                [
                    [null],
                    [$initializer],
                ]
            );

        $expectedService = $this->createMock(VirtualProxyInterface::class);

        $this->proxyFactory
            ->expects(self::once())
            ->method('createProxy')
            ->willReturnCallback(
                /** @psalm-suppress UnusedVariable */
                static function (string $className, callable $initializer) use ($expectedService, $proxy): MockObject {
                    self::assertEquals('FooClass', $className);

                    try {
                        $wrappedInstance = null;
                        $initializer($wrappedInstance, $proxy);
                        self::fail('Expected exception was not thrown');
                    } catch (RuntimeException) {
                    }

                    return $expectedService;
                }
            );

        $result = $this->factory->__invoke($this->container, 'fooService', $callback);
        self::assertSame($expectedService, $result);
    }
}
