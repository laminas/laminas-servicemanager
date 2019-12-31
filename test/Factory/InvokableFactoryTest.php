<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers \Laminas\ServiceManager\Factory\InvokableFactory
 */
class InvokableFactoryTest extends TestCase
{
    public function testCanCreateObject()
    {
        $container = $this->getMock(ContainerInterface::class);
        $factory   = new InvokableFactory();

        $object = $factory($container, InvokableObject::class, ['foo' => 'bar']);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }
}
