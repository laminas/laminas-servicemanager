<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Di;

use Laminas\ServiceManager\Di\DiAbstractServiceFactory;
use Laminas\ServiceManager\ServiceManager;

class DiAbstractServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DiAbstractServiceFactory
     */
    protected $diAbstractServiceFactory = null;

    /**@#+
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockDi = null;
    protected $mockServiceLocator = null;
    /**@#-*/

    protected $fooInstance = null;

    public function setup()
    {
        $instanceManager = new \Laminas\Di\InstanceManager();
        $instanceManager->addSharedInstance($this->fooInstance = new \stdClass(), 'foo');
        $this->mockDi = $this->getMock('Laminas\Di\Di', array(), array(null, $instanceManager));
        $this->mockServiceLocator = $this->getMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $this->diAbstractServiceFactory = new DiAbstractServiceFactory(
            $this->mockDi
        );
    }


    /**
     * @covers Laminas\ServiceManager\Di\DiAbstractServiceFactory::__construct
     */
    public function testConstructor()
    {
        $instance = new DiAbstractServiceFactory(
            $this->getMock('Laminas\Di\Di')
        );
        $this->assertInstanceOf('Laminas\ServiceManager\Di\DiAbstractServiceFactory', $instance);
    }

    /**
     * @covers Laminas\ServiceManager\Di\DiAbstractServiceFactory::createServiceWithName
     * @covers Laminas\ServiceManager\Di\DiAbstractServiceFactory::get
     */
    public function testCreateServiceWithName()
    {
        $foo = $this->diAbstractServiceFactory->createServiceWithName($this->mockServiceLocator, 'foo', 'foo');
        $this->assertEquals($this->fooInstance, $foo);
    }

    /**
     * @covers Laminas\ServiceManager\Di\DiAbstractServiceFactory::canCreateServiceWithName
     */
    public function testCanCreateServiceWithName()
    {
        $instance = new DiAbstractServiceFactory($this->getMock('Laminas\Di\Di'));
        $im = $instance->instanceManager();

        $locator = new ServiceManager();

        // will check shared instances
        $this->assertFalse($instance->canCreateServiceWithName($locator, 'a-shared-instance-alias', 'a-shared-instance-alias'));
        $im->addSharedInstance(new \stdClass(), 'a-shared-instance-alias');
        $this->assertTrue($instance->canCreateServiceWithName($locator, 'a-shared-instance-alias', 'a-shared-instance-alias'));

        // will check aliases
        $this->assertFalse($instance->canCreateServiceWithName($locator, 'an-alias', 'an-alias'));
        $im->addAlias('an-alias', 'stdClass');
        $this->assertTrue($instance->canCreateServiceWithName($locator, 'an-alias', 'an-alias'));

        // will check instance configurations
        $this->assertFalse($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\Non\Existing', __NAMESPACE__ . '\Non\Existing'));
        $im->setConfig(__NAMESPACE__ . '\Non\Existing', array('parameters' => array('a' => 'b')));
        $this->assertTrue($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\Non\Existing', __NAMESPACE__ . '\Non\Existing'));

        // will check preferences for abstract types
        $this->assertFalse($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\AbstractClass', __NAMESPACE__ . '\AbstractClass'));
        $im->setTypePreference(__NAMESPACE__ . '\AbstractClass', array(__NAMESPACE__ . '\Non\Existing'));
        $this->assertTrue($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\AbstractClass', __NAMESPACE__ . '\AbstractClass'));

        // will check definitions
        $def = $instance->definitions();
        $this->assertFalse($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\Other\Non\Existing', __NAMESPACE__ . '\Other\Non\Existing'));
        $classDefinition = $this->getMock('Laminas\Di\Definition\DefinitionInterface');
        $classDefinition
            ->expects($this->any())
            ->method('hasClass')
            ->with($this->equalTo(__NAMESPACE__ . '\Other\Non\Existing'))
            ->will($this->returnValue(true));
        $def->addDefinition($classDefinition);
        $this->assertTrue($instance->canCreateServiceWithName($locator, __NAMESPACE__ . '\Other\Non\Existing', __NAMESPACE__ . '\Other\Non\Existing'));
    }
}
