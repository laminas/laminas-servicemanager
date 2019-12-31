<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\PsrContainerDecorator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @covers \Laminas\ServiceManager\PsrContainerDecorator
 */
class PsrContainerDecoratorTest extends TestCase
{
    public function testProxiesHasToDecoratedContainer()
    {
        $psrContainer = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $psrContainer->expects($this->exactly(2))
            ->method('has')
            ->with('string key')
            ->willReturnOnConsecutiveCalls(true, false);
        $decorator = new PsrContainerDecorator($psrContainer);
        $this->assertTrue($decorator->has('string key'));
        $this->assertFalse($decorator->has('string key'));
    }

    public function testProxiesGetToDecoratedContainer()
    {
        $service = new stdClass();
        $psrContainer = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $psrContainer->expects($this->once())
            ->method('get')
            ->with('string key')
            ->willReturn($service);
        $decorator = new PsrContainerDecorator($psrContainer);
        $this->assertSame($service, $decorator->get('string key'));
    }

    public function testGetterReturnsDecoratedContainer()
    {
        $psrContainer = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $decorator = new PsrContainerDecorator($psrContainer);
        $this->assertSame($psrContainer, $decorator->getContainer());
    }
}
