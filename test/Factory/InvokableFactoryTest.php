<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\Factory\InvokableFactory
 */
class InvokableFactoryTest extends TestCase
{
    public function testCanCreateObject()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $factory   = new InvokableFactory();

        $object = $factory($container, InvokableObject::class, ['foo' => 'bar']);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }
}
