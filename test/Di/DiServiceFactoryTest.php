<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Di;

use Laminas\Di\Di;
use Laminas\ServiceManager\Di\DiServiceFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * @group Laminas_ServiceManager
 */
class DiServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DiServiceFactory
     */
    protected $diServiceFactory = null;

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
        $instanceManager->addSharedInstanceWithParameters(
            $this->fooInstance = new \stdClass(),
            'foo',
            array('bar' => 'baz')
        );
        $this->mockDi = $this->getMock('Laminas\Di\Di', array(), array(null, $instanceManager));
        $this->mockServiceLocator = $this->getMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $this->diServiceFactory = new DiServiceFactory(
            $this->mockDi,
            'foo',
            array('bar' => 'baz')
        );
    }

    /**
     * @covers Laminas\ServiceManager\Di\DiServiceFactory::__construct
     */
    public function testConstructor()
    {
        $instance = new DiServiceFactory(
            $this->getMock('Laminas\Di\Di'),
            'string',
            array('foo' => 'bar')
        );
        $this->assertInstanceOf('Laminas\ServiceManager\Di\DiServiceFactory', $instance);
    }

    /**
     * @covers Laminas\ServiceManager\Di\DiServiceFactory::createService
     * @covers Laminas\ServiceManager\Di\DiServiceFactory::get
     */
    public function testCreateService()
    {
        $foo = $this->diServiceFactory->createService($this->mockServiceLocator);
        $this->assertEquals($this->fooInstance, $foo);
    }
}
