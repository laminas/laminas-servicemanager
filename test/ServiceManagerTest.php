<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use Laminas\Di\Di;
use Laminas\Mvc\Service\DiFactory;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Di\DiAbstractServiceFactory;
use Laminas\ServiceManager\Exception;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\FooCounterAbstractFactory;

class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ServiceManager
     */
    protected $serviceManager = null;

    public function setup()
    {
        $this->serviceManager = new ServiceManager;
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::__construct
     */
    public function testConstructorConfig()
    {
        $config = new Config(array('services' => array('foo' => 'bar')));
        $serviceManager = new ServiceManager($config);
        $this->assertEquals('bar', $serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAllowOverride
     * @covers Laminas\ServiceManager\ServiceManager::getAllowOverride
     */
    public function testAllowOverride()
    {
        $this->assertFalse($this->serviceManager->getAllowOverride());
        $ret = $this->serviceManager->setAllowOverride(true);
        $this->assertSame($this->serviceManager, $ret);
        $this->assertTrue($this->serviceManager->getAllowOverride());
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setThrowExceptionInCreate
     * @covers Laminas\ServiceManager\ServiceManager::getThrowExceptionInCreate
     */
    public function testThrowExceptionInCreate()
    {
        $this->assertTrue($this->serviceManager->getThrowExceptionInCreate());
        $ret = $this->serviceManager->setThrowExceptionInCreate(false);
        $this->assertSame($this->serviceManager, $ret);
        $this->assertFalse($this->serviceManager->getThrowExceptionInCreate());
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setInvokableClass
     */
    public function testSetInvokableClass()
    {
        $ret = $this->serviceManager->setInvokableClass('foo', 'bar');
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setFactory
     */
    public function testSetFactory()
    {
        $ret = $this->serviceManager->setFactory('foo', 'bar');
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setFactory
     */
    public function testSetFactoryThrowsExceptionOnDuplicate()
    {
        $this->serviceManager->setFactory('foo', 'bar');
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidServiceNameException');
        $this->serviceManager->setFactory('foo', 'bar');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::addAbstractFactory
     */
    public function testAddAbstractFactory()
    {
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\FooAbstractFactory');

        $ret = $this->serviceManager->addAbstractFactory(new TestAsset\FooAbstractFactory());
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::addAbstractFactory
     */
    public function testAddAbstractFactoryThrowsExceptionOnInvalidFactory()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidArgumentException');
        $this->serviceManager->addAbstractFactory(10);
    }

    public function testServiceManagerIsPassedToInitializer()
    {
        $initializer = new TestAsset\FooInitializer();
        $this->serviceManager->addInitializer($initializer);
        $this->serviceManager->setFactory('foo', function () {
            return new \stdClass();
        });
        $obj = $this->serviceManager->get('foo');
        $this->assertSame($this->serviceManager, $initializer->sm);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::addInitializer
     */
    public function testAddInitializer()
    {
        $ret = $this->serviceManager->addInitializer(new TestAsset\FooInitializer());
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::addInitializer
     */
    public function testAddInitializerThrowsExceptionOnInvalidInitializer()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidArgumentException');
        $this->serviceManager->addInitializer(5);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setService
     */
    public function testSetService()
    {
        $ret = $this->serviceManager->setService('foo', 'bar');
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setShared
     */
    public function testSetShared()
    {
        $this->serviceManager->setInvokableClass('foo', 'bar');
        $ret = $this->serviceManager->setShared('foo', true);
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setShared
     */
    public function testSetSharedAbstractFactory()
    {
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\FooAbstractFactory');
        $ret = $this->serviceManager->setShared('foo', false);
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setShared
     */
    public function testSetSharedThrowsExceptionOnUnregisteredService()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceNotFoundException');
        $this->serviceManager->setShared('foo', true);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGet()
    {
        $this->serviceManager->setService('foo', 'bar');
        $this->assertEquals('bar', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetDoesNotThrowExceptionOnEmptyArray()
    {
        $this->serviceManager->setService('foo', array());
        $this->serviceManager->get('foo');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetThrowsExceptionOnUnknownService()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceNotFoundException');
        $this->assertEquals('bar', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetWithAlias()
    {
        $this->serviceManager->setService('foo', 'bar');
        $this->serviceManager->setAlias('baz', 'foo');
        $this->assertEquals('bar', $this->serviceManager->get('baz'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetWithScopedContainer()
    {
        $this->serviceManager->setService('foo', 'bar');
        $scopedServiceManager = $this->serviceManager->createScopedServiceManager();
        $this->assertEquals('bar', $scopedServiceManager->get('foo'));
    }

    public function testCanRetrieveFromParentPeeringManager()
    {
        $parent = new ServiceManager();
        $parent->setService('foo', 'bar');
        $child  = new ServiceManager();
        $child->addPeeringServiceManager($parent, ServiceManager::SCOPE_PARENT);
        $this->assertEquals('bar', $child->get('foo'));
    }

    public function testCanRetrieveFromChildPeeringManager()
    {
        $parent = new ServiceManager();
        $child  = new ServiceManager();
        $child->addPeeringServiceManager($parent, ServiceManager::SCOPE_CHILD);
        $child->setService('foo', 'bar');
        $this->assertEquals('bar', $parent->get('foo'));
    }

    public function testAllowsRetrievingFromPeeringContainerFirst()
    {
        $parent = new ServiceManager();
        $parent->setFactory('foo', function ($sm) {
            return 'bar';
        });
        $child  = new ServiceManager();
        $child->setFactory('foo', function ($sm) {
            return 'baz';
        });
        $child->addPeeringServiceManager($parent, ServiceManager::SCOPE_PARENT);
        $child->setRetrieveFromPeeringManagerFirst(true);
        $this->assertEquals('bar', $child->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testCreateWithInvokableClass()
    {
        $this->serviceManager->setInvokableClass('foo', 'LaminasTest\ServiceManager\TestAsset\Foo');
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Foo', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testCreateWithFactoryInstance()
    {
        $this->serviceManager->setFactory('foo', 'LaminasTest\ServiceManager\TestAsset\FooFactory');
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Foo', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testCreateWithCallableFactory()
    {
        $this->serviceManager->setFactory('foo', function () { return new TestAsset\Foo; });
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Foo', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testCreateWithAbstractFactory()
    {
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\FooAbstractFactory');
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Foo', $this->serviceManager->get('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testCreateWithMultipleAbstractFactories()
    {
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\BarAbstractFactory');
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\FooAbstractFactory');

        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Bar', $this->serviceManager->get('bar'));
    }

    public function testCreateWithInitializerObject()
    {
        $this->serviceManager->addInitializer(new TestAsset\FooInitializer(array('foo' => 'bar')));
        $this->serviceManager->setFactory('foo', function () {
            return new \stdClass();
        });
        $obj = $this->serviceManager->get('foo');
        $this->assertEquals('bar', $obj->foo);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::has
     */
    public function testHas()
    {
        $this->assertFalse($this->serviceManager->has('foo'));
        $this->serviceManager->setInvokableClass('foo', 'bar');
        $this->assertTrue($this->serviceManager->has('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testSetAlias()
    {
        $this->serviceManager->setInvokableClass('foo', 'bar');
        $ret = $this->serviceManager->setAlias('bar', 'foo');
        $this->assertSame($this->serviceManager, $ret);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testSetAliasThrowsExceptionOnInvalidAliasName()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidServiceNameException');
        $this->serviceManager->setAlias(5, 10);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testSetAliasThrowsExceptionOnEmptyAliasName()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidServiceNameException');
        $this->serviceManager->setAlias('', 'foo');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testSetAliasThrowsExceptionOnDuplicateAlias()
    {
        $this->serviceManager->setService('foo', 'bar');
        $this->serviceManager->setAlias('baz', 'foo');

        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidServiceNameException');
        $this->serviceManager->setAlias('baz', 'foo');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testSetAliasDoesNotThrowExceptionOnServiceNotFound()
    {
        $this->serviceManager->setAlias('foo', 'bar');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetServiceThrowsExceptionOnAliasWithNoSetService()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceNotFoundException');
        $this->serviceManager->setAlias('foo', 'bar');
        $this->serviceManager->get('foo');
    }

    /**
     * @cover Laminas\ServiceManager\ServiceManager::get
     */
    public function testGetServiceThrowsExceptionOnMultipleAliasesWithNoSetService()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\ServiceNotFoundException');
        $this->serviceManager->setAlias('foo', 'bar');
        $this->serviceManager->setAlias('baz', 'foo');
        $this->serviceManager->get('foo');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::hasAlias
     */
    public function testHasAlias()
    {
        $this->assertFalse($this->serviceManager->hasAlias('foo'));

        $this->serviceManager->setService('bar', 'baz');
        $this->serviceManager->setAlias('foo', 'bar');
        $this->assertTrue($this->serviceManager->hasAlias('foo'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::createScopedServiceManager
     */
    public function testCreateScopedServiceManager()
    {
        $this->serviceManager->setService('foo', 'bar');
        $scopedServiceManager = $this->serviceManager->createScopedServiceManager();
        $this->assertNotSame($this->serviceManager, $scopedServiceManager);
        $this->assertFalse($scopedServiceManager->has('foo', true, false));

        $this->assertContains($this->serviceManager, $this->readAttribute($scopedServiceManager, 'peeringServiceManagers'));

        // test child scoped
        $childScopedServiceManager = $this->serviceManager->createScopedServiceManager(ServiceManager::SCOPE_CHILD);
        $this->assertContains($childScopedServiceManager, $this->readAttribute($this->serviceManager, 'peeringServiceManagers'));
    }

    public function testConfigureWithInvokableClass()
    {
        $config = new Config(array(
            'invokables' => array(
                'foo' => 'LaminasTest\ServiceManager\TestAsset\Foo',
            ),
        ));
        $serviceManager = new ServiceManager($config);
        $foo = $serviceManager->get('foo');
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Foo', $foo);
    }

    public function testPeeringService()
    {
        $di = new Di();
        $di->instanceManager()->setParameters('LaminasTest\ServiceManager\TestAsset\Bar', array('foo' => array('a')));
        $this->serviceManager->addAbstractFactory(new DiAbstractServiceFactory($di));
        $sm = $this->serviceManager->createScopedServiceManager(ServiceManager::SCOPE_PARENT);
        $sm->setFactory('di', new DiFactory());
        $bar = $sm->get('LaminasTest\ServiceManager\TestAsset\Bar', true);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Bar', $bar);
    }

    public function testDiAbstractServiceFactory()
    {
        $di = $this->getMock('Laminas\Di\Di');
        $factory = new DiAbstractServiceFactory($di);
        $factory->instanceManager()->setConfig('LaminasTest\ServiceManager\TestAsset\Bar', array('parameters' => array('foo' => array('a'))));
        $this->serviceManager->addAbstractFactory($factory);

        $this->assertTrue($this->serviceManager->has('LaminasTest\ServiceManager\TestAsset\Bar', true));

        $bar = $this->serviceManager->get('LaminasTest\ServiceManager\TestAsset\Bar', true);
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Bar', $bar);
    }

    public function testExceptionThrowingFactory()
    {
        $this->serviceManager->setFactory('foo', 'LaminasTest\ServiceManager\TestAsset\ExceptionThrowingFactory');
        try {
            $this->serviceManager->get('foo');
            $this->fail("No exception thrown");
        } catch (Exception\ServiceNotCreatedException $e) {
            $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\FooException', $e->getPrevious());
        }
    }

    /**
     * @expectedException Laminas\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testCannotUseUnknownServiceNameForAbstractFactory()
    {
        $config = new Config(array(
            'abstract_factories' => array(
                'LaminasTest\ServiceManager\TestAsset\FooAbstractFactory',
            ),
        ));
        $serviceManager = new ServiceManager($config);
        $serviceManager->setFactory('foo', 'LaminasTest\ServiceManager\TestAsset\FooFactory');
        $foo = $serviceManager->get('unknownObject');
    }

    /**
     * @expectedException Laminas\ServiceManager\Exception\ServiceNotCreatedException
     */
    public function testDoNotFallbackToAbstractFactory()
    {
        $factory = function ($sm) {
            return new TestAsset\Bar();
        };
        $serviceManager = new ServiceManager();
        $serviceManager->setFactory('LaminasTest\ServiceManager\TestAsset\Bar', $factory);
        $di = new Di();
        $di->instanceManager()->setParameters('LaminasTest\ServiceManager\TestAsset\Bar', array('foo' => array('a')));
        $serviceManager->addAbstractFactory(new DiAbstractServiceFactory($di));
        $bar = $serviceManager->get('LaminasTest\ServiceManager\TestAsset\Bar');
    }

    /**
     * @expectedException Laminas\ServiceManager\Exception\InvalidServiceNameException
     */
    public function testAssignAliasWithExistingServiceName()
    {
        $this->serviceManager->setFactory('foo', 'LaminasTest\ServiceManager\TestAsset\FooFactory');
        $this->serviceManager->setFactory('bar', function ($sm) {
                return new Bar(array('a'));
            });
        $this->serviceManager->setAllowOverride(false);
        // should throw an exception because 'foo' already exists in the service manager
        $this->serviceManager->setAlias('foo', 'bar');
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::createFromAbstractFactory
     * @covers Laminas\ServiceManager\ServiceManager::has
     */
    public function testWillNotCreateCircularReferences()
    {
        $abstractFactory = new TestAsset\CircularDependencyAbstractFactory();
        $sm = new ServiceManager();
        $sm->addAbstractFactory($abstractFactory);
        $foo = $sm->get('foo');
        $this->assertSame($abstractFactory->expectedInstance, $foo);
    }

    public function testShouldAllowAddingInitializersAsClassNames()
    {
        $result = $this->serviceManager->addInitializer('LaminasTest\ServiceManager\TestAsset\FooInitializer');
        $this->assertSame($this->serviceManager, $result);
    }

    public function testShouldRaiseExceptionIfInitializerClassIsNotAnInitializerInterfaceImplementation()
    {
        $this->setExpectedException('Laminas\ServiceManager\Exception\InvalidArgumentException');
        $result = $this->serviceManager->addInitializer(get_class($this));
    }

    public function duplicateService()
    {
        $self = $this;

        return array(
            array(
                'setFactory',
                function ($services) use ($self) {
                    return $self;
                },
                $self,
                'assertSame',
            ),
            array(
                'setInvokableClass',
                'stdClass',
                'stdClass',
                'assertInstanceOf',
            ),
            array(
                'setService',
                $self,
                $self,
                'assertSame',
            ),
        );
    }

    /**
     * @dataProvider duplicateService
     */
    public function testWithAllowOverrideOnRegisteringAServiceDuplicatingAnExistingAliasShouldInvalidateTheAlias($method, $service, $expected, $assertion = 'assertSame')
    {
        $this->serviceManager->setAllowOverride(true);
        $sm = $this->serviceManager;
        $this->serviceManager->setFactory('http.response', function () use ($sm) {
            return $sm;
        });
        $this->serviceManager->setAlias('response', 'http.response');
        $this->assertSame($sm, $this->serviceManager->get('response'));

        $this->serviceManager->{$method}('response', $service);
        $this->{$assertion}($expected, $this->serviceManager->get('response'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::canonicalizeName
     */
    public function testCanonicalizeName()
    {
        $this->serviceManager->setService('foo_bar', new \stdClass());
        $this->assertEquals(true, $this->serviceManager->has('foo_bar'));
        $this->assertEquals(true, $this->serviceManager->has('foobar'));
        $this->assertEquals(true, $this->serviceManager->has('foo-bar'));
        $this->assertEquals(true, $this->serviceManager->has('foo/bar'));
        $this->assertEquals(true, $this->serviceManager->has('foo bar'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::canCreateFromAbstractFactory
     */
    public function testWanCreateFromAbstractFactoryWillNotInstantiateAbstractFactoryOnce()
    {
        $count = FooCounterAbstractFactory::$instantiationCount;
        $this->serviceManager->addAbstractFactory(__NAMESPACE__ . '\TestAsset\FooCounterAbstractFactory');

        $this->serviceManager->canCreateFromAbstractFactory('foo', 'foo');
        $this->serviceManager->canCreateFromAbstractFactory('foo', 'foo');

        $this->assertSame($count + 1, FooCounterAbstractFactory::$instantiationCount);
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::canCreateFromAbstractFactory
     * @covers Laminas\ServiceManager\ServiceManager::create
     */
    public function testAbstractFactoryNotUsedIfNotAbleToCreate()
    {
        $service = new \stdClass;

        $af1 = $this->getMock('Laminas\ServiceManager\AbstractFactoryInterface');
        $af1->expects($this->any())->method('canCreateServiceWithName')->will($this->returnValue(true));
        $af1->expects($this->any())->method('createServiceWithName')->will($this->returnValue($service));

        $af2 = $this->getMock('Laminas\ServiceManager\AbstractFactoryInterface');
        $af2->expects($this->any())->method('canCreateServiceWithName')->will($this->returnValue(false));
        $af2->expects($this->never())->method('createServiceWithName');

        $this->serviceManager->addAbstractFactory($af1);
        $this->serviceManager->addAbstractFactory($af2);

        $this->assertSame($service, $this->serviceManager->create('test'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::setAlias
     * @covers Laminas\ServiceManager\ServiceManager::get
     * @covers Laminas\ServiceManager\ServiceManager::retrieveFromPeeringManager
     */
    public function testCanGetAliasedServicesFromPeeringServiceManagers()
    {
        $service   = new \stdClass();
        $peeringSm = new ServiceManager();

        $peeringSm->setService('actual-service-name', $service);
        $this->serviceManager->addPeeringServiceManager($peeringSm);

        $this->serviceManager->setAlias('alias-name', 'actual-service-name');

        $this->assertSame($service, $this->serviceManager->get('alias-name'));
    }

    /**
     * @covers Laminas\ServiceManager\ServiceManager::get
     */
    public function testDuplicateNewInstanceMultipleAbstractFactories()
    {
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setShareByDefault(false);
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\BarAbstractFactory');
        $this->serviceManager->addAbstractFactory('LaminasTest\ServiceManager\TestAsset\FooAbstractFactory');
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Bar', $this->serviceManager->get('bar'));
        $this->assertInstanceOf('LaminasTest\ServiceManager\TestAsset\Bar', $this->serviceManager->get('bar'));
    }
}
