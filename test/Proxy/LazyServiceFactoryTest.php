<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Proxy;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\VirtualProxyInterface;
use Psr\Container\ContainerInterface;

/**
 * @covers \Laminas\ServiceManager\Proxy\LazyServiceFactory
 */
class LazyServiceFactoryTest extends TestCase
{
    private LazyServiceFactory $factory;

    /** @var LazyLoadingValueHolderFactory&MockObject */
    private $proxyFactory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->proxyFactory = $this->getMockBuilder(LazyLoadingValueHolderFactory::class)
            ->getMock();
        $servicesMap        = [
            'fooService' => 'FooClass',
        ];

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->factory = new LazyServiceFactory($this->proxyFactory, $servicesMap);
    }

    public function testImplementsDelegatorFactoryInterface(): void
    {
        $this->assertInstanceOf(DelegatorFactoryInterface::class, $this->factory);
    }

    public function testThrowExceptionWhenServiceNotExists(): void
    {
        $callback = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $callback->expects($this->never())
            ->method('callback');

        $container = $this->createContainerMock();

        $this->proxyFactory->expects($this->never())
            ->method('createProxy');
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('The requested service "not_exists" was not found in the provided services map');

        $this->factory->__invoke($container, 'not_exists', [$callback, 'callback']);
    }

    public function testCreates(): void
    {
        $callback = $this->getMockBuilder('stdClass')
            ->setMethods(['callback'])
            ->getMock();
        $callback->expects($this->once())
            ->method('callback')
            ->willReturn('fooValue');
        $container       = $this->createContainerMock();
        $expectedService = $this->getMockBuilder(VirtualProxyInterface::class)
            ->getMock();

        $this->proxyFactory->expects($this->once())
            ->method('createProxy')
            ->willReturnCallback(
                function ($className, $initializer) use ($expectedService) {
                    $this->assertEquals('FooClass', $className, 'class name not match');

                    $wrappedInstance = null;
                    $result          = $initializer(
                        $wrappedInstance,
                        $this->getMockBuilder(LazyLoadingInterface::class)->getMock()
                    );

                    $this->assertEquals('fooValue', $wrappedInstance, 'expected callback return value');
                    $this->assertTrue($result, 'initializer should return true');

                    return $expectedService;
                }
            );

        $result = $this->factory->__invoke($container, 'fooService', [$callback, 'callback']);

        $this->assertSame($expectedService, $result, 'service created not match the expected');
    }

    /**
     * @return ContainerInterface&MockObject
     */
    private function createContainerMock(): ContainerInterface
    {
        return $this->createMock(ContainerInterface::class);
    }
}
