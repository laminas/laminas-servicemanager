<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Exception\RuntimeException;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\FooPluginManager;
use LaminasTest\ServiceManager\TestAsset\MockSelfReturningDelegatorFactory;
use ReflectionClass;
use ReflectionObject;

class AbstractPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var FooPluginManager
     */
    protected $pluginManager;

    public function setup()
    {
        $this->serviceManager = new ServiceManager();
        $this->pluginManager = new FooPluginManager(new Config([
            'factories' => [
                'Foo' => 'LaminasTest\ServiceManager\TestAsset\FooFactory',
            ],
            'shared' => [
                'Foo' => false,
            ],
        ]));
    }

    public function testSetMultipleCreationOptions()
    {
        $pluginManager = new FooPluginManager(new Config([
            'factories' => [
                'Foo' => 'LaminasTest\ServiceManager\TestAsset\FooFactory'
            ],
            'shared' => [
                'Foo' => false
            ]
        ]));

        $refl         = new ReflectionClass($pluginManager);
        $reflProperty = $refl->getProperty('factories');
        $reflProperty->setAccessible(true);

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInternalType('string', $value['foo']);

        $pluginManager->get('Foo', ['key1' => 'value1']);

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\FooFactory', $value['foo']);
        $this->assertEquals(['key1' => 'value1'], $value['foo']->getCreationOptions());

        $pluginManager->get('Foo', ['key2' => 'value2']);

        $value = $reflProperty->getValue($pluginManager);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\FooFactory', $value['foo']);
        $this->assertEquals(['key2' => 'value2'], $value['foo']->getCreationOptions());
    }

    /**
     * @group issue-4208
     */
    public function testGetFaultyRegisteredInvokableThrowsException()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceNotFoundException');

        $pluginManager = new FooPluginManager();
        $pluginManager->setInvokableClass('helloWorld', 'IDoNotExist');
        $pluginManager->get('helloWorld');
    }

    public function testAbstractFactoryWithMutableCreationOptions()
    {
        $creationOptions = ['key1' => 'value1'];
        $mock = 'LaminasTest\ServiceManager\TestAsset\AbstractFactoryWithMutableCreationOptions';
        $abstractFactory = $this->getMock($mock, ['setCreationOptions']);
        $abstractFactory->expects($this->once())
            ->method('setCreationOptions')
            ->with($creationOptions);

        $this->pluginManager->addAbstractFactory($abstractFactory);
        $instance = $this->pluginManager->get('classnoexists', $creationOptions);
        $this->assertInternalType('object', $instance);
    }

    public function testMutableMethodNeverCalledWithoutCreationOptions()
    {
        $mock = 'LaminasTest\ServiceManager\TestAsset\CallableWithMutableCreationOptions';
        $callable = $this->getMock($mock, ['setCreationOptions']);
        $callable->expects($this->never())
            ->method('setCreationOptions');

        $ref = new ReflectionObject($this->pluginManager);

        $method = $ref->getMethod('createServiceViaCallback');
        $method->setAccessible(true);
        $method->invoke($this->pluginManager, $callable, 'foo', 'bar');
    }

    public function testCallableObjectWithMutableCreationOptions()
    {
        $creationOptions = ['key1' => 'value1'];
        $mock = 'LaminasTest\ServiceManager\TestAsset\CallableWithMutableCreationOptions';
        $callable = $this->getMock($mock, ['setCreationOptions']);
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

    public function testValidatePluginIsCalledWithDelegatorFactoryIfItsAService()
    {
        $pluginManager = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');
        $delegatorFactory = $this->getMock('Laminas\\ServiceManager\\DelegatorFactoryInterface');

        $pluginManager->setService('delegator-factory', $delegatorFactory);
        $pluginManager->addDelegator('foo-service', 'delegator-factory');

        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($delegatorFactory);

        $pluginManager->create('foo-service');
    }

    public function testSingleDelegatorUsage()
    {
        $delegatorFactory = $this->getMock('Laminas\\ServiceManager\\DelegatorFactoryInterface');
        /* @var $pluginManager \Laminas\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject */
        $pluginManager = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');
        $realService = $this->getMock('stdClass', [], [], 'RealService');
        $delegator = $this->getMock('stdClass', [], [], 'Delegator');

        $delegatorFactory
            ->expects($this->once())
            ->method('createDelegatorWithName')
            ->with(
                $pluginManager,
                'fooservice',
                'foo-service',
                $this->callback(function ($callback) use ($realService) {
                    if (!is_callable($callback)) {
                        return false;
                    }

                    return call_user_func($callback) === $realService;
                })
            )
            ->will($this->returnValue($delegator));

        $pluginManager->setFactory('foo-service', function () use ($realService) {
            return $realService;
        });
        $pluginManager->addDelegator('foo-service', $delegatorFactory);

        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($delegator);

        $this->assertSame($delegator, $pluginManager->get('foo-service'));
    }

    public function testMultipleDelegatorsUsage()
    {
        /* @var $pluginManager \Laminas\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject */
        $pluginManager = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');

        $fooDelegator = new MockSelfReturningDelegatorFactory();
        $barDelegator = new MockSelfReturningDelegatorFactory();

        $pluginManager->addDelegator('foo-service', $fooDelegator);
        $pluginManager->addDelegator('foo-service', $barDelegator);
        $pluginManager->setInvokableClass('foo-service', 'stdClass');

        $pluginManager->expects($this->once())
            ->method('validatePlugin')
            ->with($barDelegator);

        $this->assertSame($barDelegator, $pluginManager->get('foo-service'));
        $this->assertCount(1, $barDelegator->instances);
        $this->assertCount(1, $fooDelegator->instances);
        $this->assertInstanceOf('stdClass', array_shift($fooDelegator->instances));
        $this->assertSame($fooDelegator, array_shift($barDelegator->instances));
    }

    /**
     * @group 6833
     */
    public function testCanCheckInvalidServiceManagerIsUsed()
    {
        $sm = new ServiceManager();
        $sm->setService('bar', new \stdClass());

        /** @var \Laminas\ServiceManager\AbstractPluginManager $pluginManager */
        $pluginManager = new FooPluginManager();
        $pluginManager->setServiceLocator($sm);

        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceLocatorUsageException');

        $pluginManager->get('bar');

        $this->fail('A Laminas\ServiceManager\Exception\ServiceNotCreatedException is expected');
    }

    /**
     * @group 6833
     */
    public function testWillRethrowOnNonValidatedPlugin()
    {
        $sm = new ServiceManager();

        $sm->setInvokableClass('stdClass', 'stdClass');

        /** @var \Laminas\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject $pluginManager */
        $pluginManager = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');

        $pluginManager
            ->expects($this->once())
            ->method('validatePlugin')
            ->with($this->isInstanceOf('stdClass'))
            ->will($this->throwException(new RuntimeException()));

        $pluginManager->setServiceLocator($sm);

        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceLocatorUsageException');

        $pluginManager->get('stdClass');
    }

    /**
     * @group 6833
     */
    public function testWillResetAutoInvokableServiceIfNotValid()
    {
        /** @var \Laminas\ServiceManager\AbstractPluginManager|\PHPUnit_Framework_MockObject_MockObject $pluginManager */
        $pluginManager = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');

        $pluginManager
            ->expects($this->any())
            ->method('validatePlugin')
            ->will($this->throwException(new RuntimeException()));

        $pluginManager->setInvokableClass(__CLASS__, __CLASS__);

        try {
            $pluginManager->get('stdClass');

            $this->fail('Expected the plugin manager to throw a RuntimeException, none thrown');
        } catch (RuntimeException $exception) {
            $this->assertFalse($pluginManager->has('stdClass'));
        }

        try {
            $pluginManager->get(__CLASS__);

            $this->fail('Expected the plugin manager to throw a RuntimeException, none thrown');
        } catch (RuntimeException $exception) {
            $this->assertTrue($pluginManager->has(__CLASS__));
        }
    }
}
