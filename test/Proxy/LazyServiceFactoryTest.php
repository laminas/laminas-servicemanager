<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Proxy;

use Laminas\ServiceManager\Proxy\LazyServiceFactory;

/**
 * Tests for {@see \Laminas\ServiceManager\Proxy\LazyServiceFactory}
 *
 * @covers \Laminas\ServiceManager\Proxy\LazyServiceFactory
 */
class LazyServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ProxyManager\Factory\LazyLoadingValueHolderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proxyFactory;

    protected $locator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        if (!interface_exists('ProxyManager\\Proxy\\ProxyInterface')) {
            $this->markTestSkipped('Please install `ocramius/proxy-manager` to run these tests');
        }

        $this->locator      = $this->getMock('Laminas\\ServiceManager\\ServiceLocatorInterface');
        $this->proxyFactory = $this
            ->getMockBuilder('ProxyManager\\Factory\\LazyLoadingValueHolderFactory')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreateDelegatorWithRequestedName()
    {
        $instance = new \stdClass();
        $callback = function () {};
        $factory  = new LazyServiceFactory($this->proxyFactory, array('foo' => 'bar'));

        $this
            ->proxyFactory
            ->expects($this->once())
            ->method('createProxy')
            ->with('bar', $callback)
            ->will($this->returnValue($instance));

        $this->assertSame($instance, $factory->createDelegatorWithName($this->locator, 'baz', 'foo', $callback));
    }

    public function testCreateDelegatorWithCanonicalName()
    {
        $instance = new \stdClass();
        $callback = function () {};
        $factory  = new LazyServiceFactory($this->proxyFactory, array('foo' => 'bar'));

        $this
            ->proxyFactory
            ->expects($this->once())
            ->method('createProxy')
            ->with('bar', $callback)
            ->will($this->returnValue($instance));

        $this->assertSame($instance, $factory->createDelegatorWithName($this->locator, 'foo', 'baz', $callback));
    }

    public function testCannotCreateDelegatorWithNoMappedServiceClass()
    {
        $factory = new LazyServiceFactory($this->proxyFactory, array());

        $this->setExpectedException('Laminas\\ServiceManager\\Exception\\InvalidServiceNameException');

        $factory->createDelegatorWithName($this->locator, 'foo', 'baz', function () {});
    }
}
