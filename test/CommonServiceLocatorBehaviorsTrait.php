<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use DateTime;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\Exception\CyclicAliasException;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use LaminasBench\ServiceManager\BenchAsset\AbstractFactoryFoo;
use LaminasTest\ServiceManager\TestAsset\CallTimesAbstractFactory;
use LaminasTest\ServiceManager\TestAsset\FailingAbstractFactory;
use LaminasTest\ServiceManager\TestAsset\FailingExceptionWithStringAsCodeFactory;
use LaminasTest\ServiceManager\TestAsset\FailingFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\PassthroughDelegatorFactory;
use LaminasTest\ServiceManager\TestAsset\SampleFactory;
use LaminasTest\ServiceManager\TestAsset\SimpleAbstractFactory;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use stdClass;

use function array_fill_keys;
use function array_keys;
use function array_merge;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

/**
 * @see ConfigInterface
 * @see TestCase
 *
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 * @psalm-require-extends TestCase
 */
trait CommonServiceLocatorBehaviorsTrait
{
    /**
     * The creation context container; used in some mocks for comparisons; set during createContainer.
     *
     * @var ServiceManager
     */
    protected $creationContext;

    /**
     * @psalm-param ServiceManagerConfigurationType $config
     * @return ServiceManager
     */
    abstract public function createContainer(array $config = []);

    public function testIsSharedByDefault(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
        ]);

        $object1 = $serviceManager->get(stdClass::class);
        $object2 = $serviceManager->get(stdClass::class);

        $this->assertSame($object1, $object2);
    }

    public function testCanDisableSharedByDefault(): void
    {
        $serviceManager = $this->createContainer([
            'factories'         => [
                stdClass::class => InvokableFactory::class,
            ],
            'shared_by_default' => false,
        ]);

        $object1 = $serviceManager->get(stdClass::class);
        $object2 = $serviceManager->get(stdClass::class);

        $this->assertNotSame($object1, $object2);
    }

    public function testCanDisableSharedForSingleService(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
            'shared'    => [
                stdClass::class => false,
            ],
        ]);

        $object1 = $serviceManager->get(stdClass::class);
        $object2 = $serviceManager->get(stdClass::class);

        $this->assertNotSame($object1, $object2);
    }

    public function testCanEnableSharedForSingleService(): void
    {
        $serviceManager = $this->createContainer([
            'factories'         => [
                stdClass::class => InvokableFactory::class,
            ],
            'shared_by_default' => false,
            'shared'            => [
                stdClass::class => true,
            ],
        ]);

        $object1 = $serviceManager->get(stdClass::class);
        $object2 = $serviceManager->get(stdClass::class);

        $this->assertSame($object1, $object2);
    }

    public function testCanBuildObjectWithInvokableFactory(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                InvokableObject::class => InvokableFactory::class,
            ],
        ]);

        $object = $serviceManager->build(InvokableObject::class, ['foo' => 'bar']);

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertEquals(['foo' => 'bar'], $object->options);
    }

    public function testCanCreateObjectWithClosureFactory(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => function (ServiceLocatorInterface $serviceLocator, $className) {
                    $this->assertEquals(stdClass::class, $className);
                    return new stdClass();
                },
            ],
        ]);

        $object = $serviceManager->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $object);
    }

    public function testCanCreateServiceWithAbstractFactory(): void
    {
        $serviceManager = $this->createContainer([
            'abstract_factories' => [
                new SimpleAbstractFactory(),
            ],
        ]);

        $this->assertInstanceOf(DateTime::class, $serviceManager->get(DateTime::class));
    }

    public function testAllowsMultipleInstancesOfTheSameAbstractFactory(): void
    {
        CallTimesAbstractFactory::setCallTimes(0);

        $obj1 = new CallTimesAbstractFactory();
        $obj2 = new CallTimesAbstractFactory();

        $serviceManager = $this->createContainer([
            'abstract_factories' => [
                $obj1,
                $obj2,
            ],
        ]);
        $serviceManager->addAbstractFactory($obj1);
        $serviceManager->addAbstractFactory($obj2);
        $serviceManager->has(stdClass::class);

        $this->assertEquals(2, CallTimesAbstractFactory::getCallTimes());
    }

    public function testWillReUseAnExistingNamedAbstractFactoryInstance(): void
    {
        CallTimesAbstractFactory::setCallTimes(0);

        $serviceManager = $this->createContainer([
            'abstract_factories' => [
                CallTimesAbstractFactory::class,
                CallTimesAbstractFactory::class,
            ],
        ]);
        $serviceManager->addAbstractFactory(CallTimesAbstractFactory::class);
        $serviceManager->has(stdClass::class);

        $this->assertEquals(1, CallTimesAbstractFactory::getCallTimes());
    }

    public function testCanCreateServiceWithAlias(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'aliases'   => [
                'foo' => InvokableObject::class,
                'bar' => 'foo',
            ],
        ]);

        $object = $serviceManager->get('bar');

        $this->assertInstanceOf(InvokableObject::class, $object);
        $this->assertTrue($serviceManager->has('bar'));
        $this->assertFalse($serviceManager->has('baz'));
    }

    public function testCheckingServiceExistenceWithChecksAgainstAbstractFactories(): void
    {
        $serviceManager = $this->createContainer([
            'factories'          => [
                stdClass::class => InvokableFactory::class,
            ],
            'abstract_factories' => [
                new SimpleAbstractFactory(), // This one always return true
            ],
        ]);

        $this->assertTrue($serviceManager->has(stdClass::class));
        $this->assertTrue($serviceManager->has(DateTime::class));
    }

    public function testBuildNeverSharesInstances(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
            'shared'    => [
                stdClass::class => true,
            ],
        ]);

        $object1 = $serviceManager->build(stdClass::class);
        $object2 = $serviceManager->build(stdClass::class, ['foo' => 'bar']);

        $this->assertNotSame($object1, $object2);
    }

    public function testInitializersAreRunAfterCreation(): void
    {
        $initializer = $this->getMockBuilder(InitializerInterface::class)
            ->getMock();

        $serviceManager = $this->createContainer([
            'factories'    => [
                stdClass::class => InvokableFactory::class,
            ],
            'initializers' => [
                $initializer,
            ],
        ]);

        $initializer->expects($this->once())
            ->method('__invoke')
            ->with($this->creationContext, $this->isInstanceOf(stdClass::class));

        // We call it twice to make sure that the initializer is only called once

        $serviceManager->get(stdClass::class);
        $serviceManager->get(stdClass::class);
    }

    public function testThrowExceptionIfServiceCannotBeCreated(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => FailingFactory::class,
            ],
        ]);

        $this->expectException(ServiceNotCreatedException::class);

        $serviceManager->get(stdClass::class);
    }

    public function testThrowExceptionWithStringAsCodeIfServiceCannotBeCreated(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => FailingExceptionWithStringAsCodeFactory::class,
            ],
        ]);

        $this->expectException(ServiceNotCreatedException::class);

        $serviceManager->get(stdClass::class);
    }

    public function testConfigureCanAddNewServices(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                DateTime::class => InvokableFactory::class,
            ],
        ]);

        $this->assertTrue($serviceManager->has(DateTime::class));
        $this->assertFalse($serviceManager->has(stdClass::class));

        $newServiceManager = $serviceManager->configure([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
        ]);

        $this->assertSame($serviceManager, $newServiceManager);

        $this->assertTrue($newServiceManager->has(DateTime::class));
        $this->assertTrue($newServiceManager->has(stdClass::class));
    }

    public function testConfigureCanOverridePreviousSettings(): void
    {
        $firstFactory  = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();
        $secondFactory = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();

        $serviceManager = $this->createContainer([
            'factories' => [
                DateTime::class => $firstFactory,
            ],
        ]);

        $newServiceManager = $serviceManager->configure([
            'factories' => [
                DateTime::class => $secondFactory,
            ],
        ]);

        $this->assertSame($serviceManager, $newServiceManager);

        $firstFactory
            ->expects($this->never())
            ->method($this->anything());

        $date = new DateTime();
        $secondFactory
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($date);

        $dateFromServiceManager = $newServiceManager->get(DateTime::class);
        $this->assertSame($date, $dateFromServiceManager);
    }

    public function testConfigureInvokablesTakePrecedenceOverFactories(): void
    {
        $firstFactory = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();

        $serviceManager = $this->createContainer([
            'aliases'    => [
                'custom_alias' => DateTime::class,
            ],
            'factories'  => [
                DateTime::class => $firstFactory,
            ],
            'invokables' => [
                'custom_alias' => stdClass::class,
            ],
        ]);

        $firstFactory->expects($this->never())->method('__invoke');

        $object = $serviceManager->get('custom_alias');
        $this->assertInstanceOf(stdClass::class, $object);
    }

    /**
     * @group has
     */
    public function testHasReturnsFalseIfServiceNotConfigured(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
        ]);
        $this->assertFalse($serviceManager->has('Some\Made\Up\Entry'));
    }

    /**
     * @group has
     */
    public function testHasReturnsTrueIfServiceIsConfigured(): void
    {
        $serviceManager = $this->createContainer([
            'services' => [
                stdClass::class => new stdClass(),
            ],
        ]);
        $this->assertTrue($serviceManager->has(stdClass::class));
    }

    /**
     * @group has
     */
    public function testHasReturnsTrueIfFactoryIsConfigured(): void
    {
        $serviceManager = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
        ]);
        $this->assertTrue($serviceManager->has(stdClass::class));
    }

    public function abstractFactories(): array
    {
        return [
            'simple'  => [new SimpleAbstractFactory(), true],
            'failing' => [new FailingAbstractFactory(), false],
        ];
    }

    /**
     * @group has
     * @dataProvider abstractFactories
     * @param mixed $abstractFactory
     */
    public function testHasChecksAgainstAbstractFactories($abstractFactory, bool $expected): void
    {
        $serviceManager = $this->createContainer([
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $this->assertSame($expected, $serviceManager->has(DateTime::class));
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::configure
     */
    public function testCanConfigureAllServiceTypes(): void
    {
        $serviceManager = $this->createContainer([
            'services'           => [
                'config' => ['foo' => 'bar'],
            ],
            'factories'          => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators'         => [
                stdClass::class => [
                    function (ContainerInterface $container, string $name, callable $callback) {
                        $instance = $callback();
                        self::assertInstanceOf(stdClass::class, $instance);
                        $instance->foo = 'bar';
                        return $instance;
                    },
                ],
            ],
            'shared'             => [
                'config'        => true,
                stdClass::class => true,
            ],
            'aliases'            => [
                'Aliased' => stdClass::class,
            ],
            'shared_by_default'  => false,
            'abstract_factories' => [
                new SimpleAbstractFactory(),
            ],
            'initializers'       => [
                function ($container, $instance) {
                    if (! $instance instanceof stdClass) {
                        return;
                    }
                    $instance->bar = 'baz';
                },
            ],
        ]);

        $dateTime = $serviceManager->get(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $dateTime, 'DateTime service did not resolve as expected');
        $notShared = $serviceManager->get(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $notShared, 'DateTime service did not re-resolve as expected');
        $this->assertNotSame(
            $dateTime,
            $notShared,
            'Expected unshared instances for DateTime service but received shared instances'
        );

        $config = $serviceManager->get('config');
        $this->assertIsArray($config, 'Config service did not resolve as expected');
        $this->assertSame(
            $config,
            $serviceManager->get('config'),
            'Config service resolved as unshared instead of shared'
        );

        $stdClass = $serviceManager->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $stdClass, 'stdClass service did not resolve as expected');
        $this->assertSame(
            $stdClass,
            $serviceManager->get(stdClass::class),
            'stdClass service should be shared, but resolved as unshared'
        );
        $this->assertTrue(
            isset($stdClass->foo),
            'Expected delegator to inject "foo" property in stdClass service, but it was not'
        );
        $this->assertEquals('bar', $stdClass->foo, 'stdClass "foo" property was not injected correctly');
        $this->assertTrue(
            isset($stdClass->bar),
            'Expected initializer to inject "bar" property in stdClass service, but it was not'
        );
        $this->assertEquals('baz', $stdClass->bar, 'stdClass "bar" property was not injected correctly');
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::configure
     */
    public function testCanSpecifyAbstractFactoryUsingStringViaConfiguration(): void
    {
        $serviceManager = $this->createContainer([
            'abstract_factories' => [
                SimpleAbstractFactory::class,
            ],
        ]);

        $dateTime = $serviceManager->get(DateTime::class);
        $this->assertInstanceOf(DateTime::class, $dateTime);
    }

    public function invalidFactories(): array
    {
        return [
            'null'                 => [null],
            'true'                 => [true],
            'false'                => [false],
            'zero'                 => [0],
            'int'                  => [1],
            'zero-float'           => [0.0],
            'float'                => [1.1],
            'array'                => [['foo', 'bar']],
            'non-invokable-object' => [(object) ['foo' => 'bar']],
        ];
    }

    public function invalidAbstractFactories(): array
    {
        $factories                     = $this->invalidFactories();
        $factories['non-class-string'] = ['non-callable-string', 'valid class name'];
        return $factories;
    }

    /**
     * @dataProvider invalidAbstractFactories
     * @param mixed $factory
     * @covers \Laminas\ServiceManager\ServiceManager::configure
     */
    public function testPassingInvalidAbstractFactoryTypeViaConfigurationRaisesException(
        $factory,
        string $contains = 'invalid abstract factory'
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($contains);
        /** @psalm-suppress InvalidArgument */
        $this->createContainer([
            'abstract_factories' => [
                $factory,
            ],
        ]);
    }

    public function testCanSpecifyInitializerUsingStringViaConfiguration(): void
    {
        $serviceManager = $this->createContainer([
            'factories'    => [
                stdClass::class => InvokableFactory::class,
            ],
            'initializers' => [
                TestAsset\SimpleInitializer::class,
            ],
        ]);

        $instance = $serviceManager->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertTrue(isset($instance->foo), '"foo" property was not injected by initializer');
        $this->assertEquals('bar', $instance->foo, '"foo" property was not properly injected');
    }

    public function invalidInitializers(): array
    {
        $factories                     = $this->invalidFactories();
        $factories['non-class-string'] = ['non-callable-string', 'callable or an instance of'];
        return $factories;
    }

    /**
     * @dataProvider invalidInitializers
     * @param mixed $initializer
     * @covers \Laminas\ServiceManager\ServiceManager::configure
     */
    public function testPassingInvalidInitializerTypeViaConfigurationRaisesException(
        $initializer,
        string $contains = 'invalid initializer'
    ) {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($contains);
        /** @psalm-suppress InvalidArgument */
        $this->createContainer([
            'initializers' => [
                $initializer,
            ],
        ]);
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::getFactory
     */
    public function testGetRaisesExceptionWhenNoFactoryIsResolved(): void
    {
        $serviceManager = $this->createContainer();
        /** @psalm-suppress InvalidArgument */
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Unable to resolve');
        $serviceManager->get('Some\Unknown\Service');
    }

    public function invalidDelegators(): array
    {
        $invalidDelegators                        = $this->invalidFactories();
        $invalidDelegators['invalid-classname']   = ['not-a-class-name', 'invalid delegator'];
        $invalidDelegators['non-invokable-class'] = [stdClass::class];
        return $invalidDelegators;
    }

    /**
     * @param mixed $delegator
     * @dataProvider invalidDelegators
     * @covers \Laminas\ServiceManager\ServiceManager::createDelegatorFromName
     */
    public function testInvalidDelegatorShouldRaiseExceptionDuringCreation(
        $delegator,
        string $contains = 'non-callable delegator'
    ) {
        /** @psalm-suppress InvalidArgument */
        $serviceManager = $this->createContainer([
            'factories'  => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators' => [
                stdClass::class => [
                    $delegator,
                ],
            ],
        ]);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage($contains);
        $serviceManager->get(stdClass::class);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::setAlias
     */
    public function testCanInjectAliases(): void
    {
        $container = $this->createContainer([
            'factories' => [
                'foo' => function () {
                    return new stdClass();
                },
            ],
        ]);

        $container->setAlias('bar', 'foo');

        $foo = $container->get('foo');
        $bar = $container->get('bar');
        $this->assertInstanceOf(stdClass::class, $foo);
        $this->assertInstanceOf(stdClass::class, $bar);
        $this->assertSame($foo, $bar);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::setInvokableClass
     */
    public function testCanInjectInvokables(): void
    {
        $container = $this->createContainer();
        $container->setInvokableClass('foo', stdClass::class);
        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has(stdClass::class));
        $foo = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $foo);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::setFactory
     */
    public function testCanInjectFactories(): void
    {
        $instance  = new stdClass();
        $container = $this->createContainer();

        $container->setFactory('foo', function () use ($instance) {
            return $instance;
        });
        $this->assertTrue($container->has('foo'));
        $foo = $container->get('foo');
        $this->assertSame($instance, $foo);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::mapLazyService
     */
    public function testCanMapLazyServices(): void
    {
        $container = $this->createContainer();
        $container->mapLazyService('foo', self::class);
        $r = new ReflectionProperty($container, 'lazyServices');
        $r->setAccessible(true);
        $lazyServices = $r->getValue($container);
        $this->assertArrayHasKey('class_map', $lazyServices);
        $this->assertArrayHasKey('foo', $lazyServices['class_map']);
        $this->assertEquals(self::class, $lazyServices['class_map']['foo']);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::addAbstractFactory
     */
    public function testCanInjectAbstractFactories(): void
    {
        $container = $this->createContainer();
        $container->addAbstractFactory(SimpleAbstractFactory::class);
        // @todo Remove "true" flag once #49 is merged
        $this->assertTrue($container->has(stdClass::class, true));
        $instance = $container->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $instance);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::addDelegator
     */
    public function testCanInjectDelegators(): void
    {
        $container = $this->createContainer([
            'factories' => [
                'foo' => function () {
                    return new stdClass();
                },
            ],
        ]);

        $container->addDelegator('foo', function ($container, $name, $callback) {
            $instance       = $callback();
            $instance->name = $name;
            return $instance;
        });

        $foo = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $foo);
        $this->assertSame('foo', $foo->name);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::addInitializer
     */
    public function testCanInjectInitializers(): void
    {
        $container = $this->createContainer([
            'factories' => [
                'foo' => function () {
                    return new stdClass();
                },
            ],
        ]);
        $container->addInitializer(function ($container, $instance) {
            if (! $instance instanceof stdClass) {
                return;
            }
            $instance->name = stdClass::class;
            return $instance;
        });

        $foo = $container->get('foo');
        $this->assertInstanceOf(stdClass::class, $foo);
        $this->assertSame(stdClass::class, $foo->name);
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::setService
     */
    public function testCanInjectServices(): void
    {
        $container = $this->createContainer();
        $container->setService('foo', $this);
        $this->assertSame($this, $container->get('foo'));
    }

    /**
     * @group mutation
     * @covers \Laminas\ServiceManager\ServiceManager::setShared
     */
    public function testCanInjectSharingRules(): void
    {
        $container = $this->createContainer([
            'factories' => [
                'foo' => function () {
                    return new stdClass();
                },
            ],
        ]);
        $container->setShared('foo', false);
        $first  = $container->get('foo');
        $second = $container->get('foo');
        $this->assertNotSame($first, $second);
    }

    public function methodsAffectedByOverrideSettings(): array
    {
        //  name                        => [ 'method to invoke',  [arguments for invocation]]
        return [
            'setAlias'                  => ['setAlias', ['foo', 'bar']],
            'setInvokableClass'         => ['setInvokableClass', ['foo', self::class]],
            'setFactory'                => [
                'setFactory',
                [
                    'foo',
                    function () {
                    },
                ],
            ],
            'setService'                => ['setService', ['foo', $this]],
            'setShared'                 => ['setShared', ['foo', false]],
            'mapLazyService'            => ['mapLazyService', ['foo', self::class]],
            'addDelegator'              => [
                'addDelegator',
                [
                    'foo',
                    function () {
                    },
                ],
            ],
            'configure-alias'           => ['configure', [['aliases' => ['foo' => 'bar']]]],
            'configure-invokable'       => ['configure', [['invokables' => ['foo' => 'foo']]]],
            'configure-invokable-alias' => ['configure', [['invokables' => ['foo' => 'bar']]]],
            'configure-factory'         => [
                'configure',
                [
                    [
                        'factories' => [
                            'foo' => function () {
                            },
                        ],
                    ],
                ],
            ],
            'configure-service'         => ['configure', [['services' => ['foo' => $this]]]],
            'configure-shared'          => ['configure', [['shared' => ['foo' => false]]]],
            'configure-lazy-service'    => [
                'configure',
                [['lazy_services' => ['class_map' => ['foo' => self::class]]]],
            ],
        ];
    }

    /**
     * @dataProvider methodsAffectedByOverrideSettings
     * @group mutation
     */
    public function testConfiguringInstanceRaisesExceptionIfAllowOverrideIsFalse(string $method, array $args): void
    {
        $container = $this->createContainer(['services' => ['foo' => $this]]);
        $container->setAllowOverride(false);
        $this->expectException(ContainerModificationsNotAllowedException::class);
        $container->$method(...$args);
    }

    /**
     * @group mutation
     */
    public function testAllowOverrideFlagIsFalseByDefault(): ContainerInterface
    {
        $container = $this->createContainer();
        $this->assertFalse($container->getAllowOverride());
        return $container;
    }

    /**
     * @group mutation
     * @depends testAllowOverrideFlagIsFalseByDefault
     */
    public function testAllowOverrideFlagIsMutable(ServiceManager $container): void
    {
        $container->setAllowOverride(true);
        $this->assertTrue($container->getAllowOverride());
    }

    /**
     * @group migration
     */
    public function testCanRetrieveParentContainerViaGetServiceLocatorWithDeprecationNotice(): void
    {
        $container = $this->createContainer();
        /** @psalm-suppress InvalidArgument */
        set_error_handler(function (int $errno) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $this->assertSame($this->creationContext, $container->getServiceLocator());
        restore_error_handler();
    }

    /**
     * @group zendframework/zend-servicemanager#83
     */
    public function testCrashesOnCyclicAliases(): void
    {
        $this->expectException(CyclicAliasException::class);

        $this->createContainer([
            'aliases' => [
                'a' => 'b',
                'b' => 'a',
            ],
        ]);
    }

    public function testMinimalCyclicAliasDefinitionShouldThrow(): void
    {
        $sm = $this->createContainer([]);

        $this->expectException(CyclicAliasException::class);
        $sm->setAlias('alias', 'alias');
    }

    public function testCoverageDepthFirstTaggingOnRecursiveAliasDefinitions(): void
    {
        $sm = $this->createContainer([
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
            'aliases'   => [
                'alias1' => 'alias2',
                'alias2' => 'alias3',
                'alias3' => stdClass::class,
            ],
        ]);
        $this->assertSame($sm->get('alias1'), $sm->get('alias2'));
        $this->assertSame($sm->get(stdClass::class), $sm->get('alias1'));
    }

    /**
     * The ServiceManager can change internal state on calls to get,
     * build or has, latter not currently. Possible state changes
     * are caching a factory, registering a service produced by
     * a factory, ...
     *
     * This tests performs three consecutive calls to build/get for
     * each registered service to push the service manager through
     * all internal states, thereby verifying that build/get/has
     * remain stable through the internal states.
     *
     * @dataProvider provideConsistencyOverInternalStatesTests
     */
    public function testConsistencyOverInternalStates(
        ContainerInterface $smTemplate,
        string $name,
        array $test,
        bool $shared
    ): void {
        $sm              = clone $smTemplate;
        $object['get']   = [];
        $object['build'] = [];

        // call get()/build() and store the retrieved
        // objects in $object['get'] or $object['build']
        // respectively
        foreach ($test as $method) {
            $obj                                   = $sm->$method($name);
            $object[$shared ? $method : 'build'][] = $obj;
            $this->assertNotNull($obj);
            $this->assertTrue($sm->has($name));
        }

        // compares the first to the first also, but ok
        foreach ($object['get'] as $sharedObj) {
            $this->assertSame($object['get'][0], $sharedObj);
        }
        // objects from object['build'] have to be different
        // from all other objects
        foreach ($object['build'] as $idx1 => $nonSharedObj1) {
            $this->assertNotContains($nonSharedObj1, $object['get']);
            foreach ($object['build'] as $idx2 => $nonSharedObj2) {
                if ($idx1 !== $idx2) {
                    $this->assertNotSame($nonSharedObj1, $nonSharedObj2);
                }
            }
        }
    }

    /**
     * Data provider
     *
     * @see testConsistencyOverInternalStates above
     */
    public function provideConsistencyOverInternalStatesTests(): array
    {
        $config1 = [
            'factories'          => [
                // to allow build('service')
                'service'   => function ($container, $requestedName, ?array $options = null) {
                    return new stdClass();
                },
                'factory'   => SampleFactory::class,
                'delegator' => SampleFactory::class,
            ],
            'delegators'         => [
                'delegator' => [
                    PassthroughDelegatorFactory::class,
                ],
            ],
            'invokables'         => [
                'invokable' => InvokableObject::class,
            ],
            'services'           => [
                'service' => new stdClass(),
            ],
            'aliases'            => [
                'serviceAlias'         => 'service',
                'invokableAlias'       => 'invokable',
                'factoryAlias'         => 'factory',
                'abstractFactoryAlias' => 'foo',
                'delegatorAlias'       => 'delegator',
            ],
            'abstract_factories' => [
                AbstractFactoryFoo::class,
            ],
        ];
        $config2                      = $config1;
        $config2['shared_by_default'] = false;

        $configs = [$config1, $config2];

        foreach ($configs as $config) {
            $smTemplates[] = $this->createContainer($config);
        }

        // produce all 3-tuples of 'build' and 'get', i.e.
        //
        // [['get', 'get', 'get'], ['get', 'get', 'build'], ...
        // ['build', 'build', 'build']]
        $methods = ['get', 'build'];
        foreach ($methods as $method1) {
            foreach ($methods as $method2) {
                foreach ($methods as $method3) {
                    $callSequences[] = [$method1, $method2, $method3];
                }
            }
        }

        foreach ($configs as $config) {
            $smTemplate = $this->createContainer($config);

            // setup sharing, services are always shared
            $names = array_fill_keys(array_keys($config['services']), true);

            // initialize the other keys with shared_by_default
            // and merge them
            $names = array_merge(array_fill_keys(array_keys(array_merge(
                $config['factories'],
                $config['invokables'],
                $config['aliases'],
                $config['delegators']
            )), $config['shared_by_default'] ?? true), $names);

            // add the key resolved by the abstract factory
            $names['foo'] = $config['shared_by_default'] ?? true;

            // adjust shared setting for individual keys from
            // $shared array if present
            if (! empty($config['shared'])) {
                foreach ($config['shared'] as $name => $shared) {
                    $names[$name] = $shared;
                }
            }

            foreach ($names as $name => $shared) {
                foreach ($callSequences as $callSequence) {
                    $sm      = clone $smTemplate;
                    $tests[] = [$smTemplate, $name, $callSequence, $shared];
                }
            }
        }
        return $tests;
    }
}
