<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Factory;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\Factory\InvokableFactory
 */
class InvokableFactoryTest extends TestCase
{
    public function testCanCreateObject(): void
    {
        $container = $this->getMockBuilder(containerinterface::class)
            ->getMock();
        $factory   = new InvokableFactory();

        $object = $factory($container, InvokableObject::class, ['foo' => 'bar']);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }
}
