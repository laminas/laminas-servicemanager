<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers \Laminas\ServiceManager\Factory\InvokableFactory
 */
class InvokableFactoryTest extends TestCase
{
    public function testCanCreateObjectWhenInvokedUsingProvidedOptions()
    {
        $container = $this->getMock(ContainerInterface::class);
        $factory   = new InvokableFactory();

        $object = $factory($container, InvokableObject::class, ['foo' => 'bar']);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }

    public function testCanCreateObjectViaCreateServiceWhenCanonicalNameIsNormalizedNameAndRequestedNameIsQualified()
    {
        $container = new ServiceManager();
        $factory   = new InvokableFactory();

        $object = $factory->createService($container, 'invokableobject', InvokableObject::class);

        $this->assertInstanceOf(InvokableObject::class, $object);
    }

    public function testCanCreateObjectViaCreateServiceWhenCanonicalNameIsQualified()
    {
        $container = new ServiceManager();
        $factory   = new InvokableFactory();

        $object = $factory->createService($container, InvokableObject::class, 'invokableobject');

        $this->assertInstanceOf(InvokableObject::class, $object);
    }

    public function testRaisesExceptionIfNeitherCanonicalNorRequestedNameAreQualified()
    {
        $container = new ServiceManager();
        $factory   = new InvokableFactory();

        $this->setExpectedException(InvalidServiceException::class);
        $object = $factory->createService($container, 'invokableobject', 'invokableobject');
    }

    public function testCreateServiceCanCreateObjectWithCreationOptionsProvidedToConstructor()
    {
        $container = new ServiceManager();
        $factory   = new InvokableFactory(['foo' => 'bar']);

        $object = $factory->createService($container, InvokableObject::class);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }
}
