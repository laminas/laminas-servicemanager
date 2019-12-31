<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\FooCounterAbstractFactory;
use LaminasTest\ServiceManager\TestAsset\FooPluginManager;
use ReflectionClass;
use ReflectionObject;

class AbstractPluginManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ServiceManager
     */
    protected $serviceManager = null;

    public function setup()
    {
        $this->serviceManager = new ServiceManager;
        $this->pluginManager = new FooPluginManager(new Config(array(
            'factories' => array(
                'Foo' => 'LaminasTest\ServiceManager\TestAsset\FooFactory',
            ),
            'shared' => array(
                'Foo' => false,
            ),
        )));
    }

    public function testSetMultipleCreationOptions()
    {
        $pluginManager = new FooPluginManager(new Config(array(
            'factories' => array(
                'Foo' => 'LaminasTest\ServiceManager\TestAsset\FooFactory'
            ),
            'shared' => array(
                'Foo' => false
            )
        )));

        $refl         = new ReflectionClass($pluginManager);
        $reflProperty = $refl->getProperty('factories');
        $reflProperty->setAccessible(true);

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInternalType('string', $value['foo']);

        $pluginManager->get('Foo', array('key1' => 'value1'));

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\FooFactory', $value['foo']);
        $this->assertEquals(array('key1' => 'value1'), $value['foo']->getCreationOptions());

        $pluginManager->get('Foo', array('key2' => 'value2'));

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\FooFactory', $value['foo']);
        $this->assertEquals(array('key2' => 'value2'), $value['foo']->getCreationOptions());
    }

    public function testAbstractFactoryWithMutableCreationOptions()
    {
        $creationOptions = array('key1' => 'value1');
        $mock = 'LaminasTest\ServiceManager\TestAsset\AbstractFactoryWithMutableCreationOptions';
        $abstractFactory = $this->getMock($mock, array('setCreationOptions'));
        $abstractFactory->expects($this->once())
            ->method('setCreationOptions')
            ->with($creationOptions);

        $this->pluginManager->addAbstractFactory($abstractFactory);
        $instance = $this->pluginManager->get('classnoexists', $creationOptions);
        $this->assertTrue(is_object($instance));
    }

    public function testMutableMethodNeverCalledWithoutCreationOptions()
    {
        $mock = 'LaminasTest\ServiceManager\TestAsset\CallableWithMutableCreationOptions';
        $callable = $this->getMock($mock, array('setCreationOptions'));
        $callable->expects($this->never())
            ->method('setCreationOptions');

        $ref = new ReflectionObject($this->pluginManager);

        $method = $ref->getMethod('createServiceViaCallback');
        $method->setAccessible(true);
        $method->invoke($this->pluginManager, $callable, 'foo', 'bar');
    }

    public function testCallableObjectWithMutableCreationOptions()
    {
        $creationOptions = array('key1' => 'value1');
        $mock = 'LaminasTest\ServiceManager\TestAsset\CallableWithMutableCreationOptions';
        $callable = $this->getMock($mock, array('setCreationOptions'));
        $callable->expects($this->once())
            ->method('setCreationOptions')
            ->with($creationOptions);

        $ref = new ReflectionObject($this->pluginManager);

        $property = $ref->getProperty('creationOptions');
        $property->setAccessible(true);
        $property->setValue($this->pluginManager, $creationOptions);

        $method = $ref->getMethod('createServiceViaCallback');
        $method->setAccessible(true);
        $method->invoke($this->pluginManager, $callable, 'foo', 'bar');
    }
}
