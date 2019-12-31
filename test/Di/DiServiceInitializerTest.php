<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Di;

use Laminas\ServiceManager\Di\DiInstanceManagerProxy;
use Laminas\ServiceManager\Di\DiServiceInitializer;

/**
 * @group Laminas_ServiceManager
 */
class DiServiceInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DiServiceInitializer
     */
    protected $diServiceInitializer = null;

    /**@#+
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockDi = null;
    protected $mockServiceLocator = null;
    protected $mockDiInstanceManagerProxy = null;
    protected $mockDiInstanceManager = null;
    /**@#-*/

    public function setup()
    {
        $this->mockDi = $this->getMock('Laminas\Di\Di', array('injectDependencies'));
        $this->mockServiceLocator = $this->getMock('Laminas\ServiceManager\ServiceLocatorInterface');
        $this->mockDiInstanceManagerProxy = new DiInstanceManagerProxy(
            $this->mockDiInstanceManager = $this->getMock('Laminas\Di\InstanceManager'),
            $this->mockServiceLocator
        );
        $this->diServiceInitializer = new DiServiceInitializer(
            $this->mockDi,
            $this->mockServiceLocator,
            $this->mockDiInstanceManagerProxy
        );

    }

    /**
     * @covers Laminas\ServiceManager\Di\DiServiceInitializer::initialize
     */
    public function testInitialize()
    {
        $instance = new \stdClass();

        // test di is called with proper instance
        $this->mockDi->expects($this->once())->method('injectDependencies')->with($instance);

        $this->diServiceInitializer->initialize($instance, $this->mockServiceLocator);
    }

    /**
     * @covers Laminas\ServiceManager\Di\DiServiceInitializer::initialize
     * @todo this needs to be moved into its own class
     */
    public function testProxyInstanceManagersStayInSync()
    {
        $instanceManager = new \Laminas\Di\InstanceManager();
        $proxy = new DiInstanceManagerProxy($instanceManager, $this->mockServiceLocator);
        $instanceManager->addAlias('foo', 'bar');

        $this->assertEquals($instanceManager->getAliases(), $proxy->getAliases());
    }

}
