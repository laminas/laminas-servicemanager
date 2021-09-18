<?php // phpcs:disable Generic.Files.LineLength.TooLong

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\PsrContainerDecorator;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimplePluginManager;
use LaminasTest\ServiceManager\TestAsset\V2v3PluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use stdClass;

use function get_class;
use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

/**
 * @covers \Laminas\ServiceManager\AbstractPluginManager
 */
class AbstractPluginManagerTest extends TestCase
{
    use CommonServiceLocatorBehaviorsTrait;
    use ProphecyTrait;

    public function createContainer(array $config = []): ServiceManager
    {
        $this->creationContext = new ServiceManager();
        return new TestAsset\LenientPluginManager($this->creationContext, $config);
    }

    public function testInjectCreationContextInFactories(): void
    {
        $invokableFactory = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();

        $config = [
            'factories' => [
                InvokableObject::class => $invokableFactory,
            ],
        ];

        $container     = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $pluginManager = new SimplePluginManager($container, $config);

        $invokableFactory->expects($this->once())
            ->method('__invoke')
            ->with($container, InvokableObject::class)
            ->will($this->returnValue(new InvokableObject()));

        $object = $pluginManager->get(InvokableObject::class);

        $this->assertInstanceOf(InvokableObject::class, $object);
    }

    public function testTransparentlyDecoratesNonInteropPsrContainerAsInteropContainer(): void
    {
        $invokableFactory = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();
        $invokableFactory->method('__invoke')
            ->will($this->returnArgument(0));

        $config = [
            'factories' => [
                'creation context container' => $invokableFactory,
            ],
        ];

        $container     = $this->getMockBuilder(PsrContainerInterface::class)
            ->getMock();
        $pluginManager = $this->getMockForAbstractClass(
            AbstractPluginManager::class,
            [$container, $config]
        );

        $object = $pluginManager->get('creation context container');

        $this->assertInstanceOf(PsrContainerDecorator::class, $object);
        $this->assertSame($container, $object->getContainer());
    }

    public function testValidateInstance(): void
    {
        $config = [
            'factories' => [
                InvokableObject::class => new InvokableFactory(),
                stdClass::class        => new InvokableFactory(),
            ],
        ];

        $container     = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $pluginManager = new SimplePluginManager($container, $config);

        // Assert no exception is triggered because the plugin manager validate ObjectWithOptions
        $pluginManager->get(InvokableObject::class);

        // Assert it throws an exception for anything else
        $this->expectException(InvalidServiceException::class);
        $pluginManager->get(stdClass::class);
    }

    public function testCachesInstanceByDefaultIfNoOptionsArePassed(): void
    {
        $config = [
            'factories' => [
                InvokableObject::class => new InvokableFactory(),
            ],
        ];

        $container     = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $pluginManager = new SimplePluginManager($container, $config);

        $first  = $pluginManager->get(InvokableObject::class);
        $second = $pluginManager->get(InvokableObject::class);
        $this->assertInstanceOf(InvokableObject::class, $first);
        $this->assertInstanceOf(InvokableObject::class, $second);
        $this->assertSame($first, $second);
    }

    public function shareByDefaultSettings(): array
    {
        return [
            'true'  => [true],
            'false' => [false],
        ];
    }

    /**
     * @dataProvider shareByDefaultSettings
     */
    public function testReturnsDiscreteInstancesIfOptionsAreProvidedRegardlessOfShareByDefaultSetting(
        bool $shareByDefault
    ): void {
        $config  = [
            'factories'        => [
                InvokableObject::class => new InvokableFactory(),
            ],
            'share_by_default' => $shareByDefault,
        ];
        $options = ['foo' => 'bar'];

        $container     = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $pluginManager = new SimplePluginManager($container, $config);

        $first  = $pluginManager->get(InvokableObject::class, $options);
        $second = $pluginManager->get(InvokableObject::class, $options);
        $this->assertInstanceOf(InvokableObject::class, $first);
        $this->assertInstanceOf(InvokableObject::class, $second);
        $this->assertNotSame($first, $second);
    }

    /**
     * Separate test from ServiceManager, as all factories go through the
     * creation context; we need to configure the parent container, as
     * the delegator factory will be receiving that.
     */
    public function testCanWrapCreationInDelegators(): void
    {
        $config         = [
            'option' => 'OPTIONED',
        ];
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => $config,
            ],
        ]);
        $pluginManager  = new TestAsset\LenientPluginManager($serviceManager, [
            'factories'  => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators' => [
                stdClass::class => [
                    TestAsset\PreDelegator::class,
                    function ($container, $name, $callback) {
                        $instance      = $callback();
                        $instance->foo = 'bar';
                        return $instance;
                    },
                ],
            ],
        ]);

        $instance = $pluginManager->get(stdClass::class);
        $this->assertInstanceOf(stdClass::class, $instance);
        $this->assertTrue(isset($instance->option), 'Delegator-injected option was not found');
        $this->assertEquals(
            $config['option'],
            $instance->option,
            'Delegator-injected option does not match configuration'
        );
        $this->assertEquals('bar', $instance->foo);
    }

    /**
     * Overrides the method in the CommonServiceLocatorBehaviorsTrait, due to behavior differences.
     *
     * @covers \Laminas\ServiceManager\AbstractPluginManager::get
     */
    public function testGetRaisesExceptionWhenNoFactoryIsResolved(): void
    {
        $pluginManager = $this->createContainer();
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(get_class($pluginManager));
        $pluginManager->get('Some\Unknown\Service');
    }

    /**
     * @group migration
     */
    public function testCallingSetServiceLocatorSetsCreationContextWithDeprecationNotice(): void
    {
        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager();
        restore_error_handler();

        $this->assertSame($pluginManager, $pluginManager->getCreationContext());
        $serviceManager = new ServiceManager();

        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager->setServiceLocator($serviceManager);
        restore_error_handler();

        $this->assertSame($serviceManager, $pluginManager->getCreationContext());
    }

    /**
     * @group migration
     */
    public function testPassingNoInitialConstructorArgumentSetsPluginManagerAsCreationContextWithDeprecationNotice(): void
    {
        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager();
        restore_error_handler();
        $this->assertSame($pluginManager, $pluginManager->getCreationContext());
    }

    /**
     * @group migration
     */
    public function testCanPassConfigInterfaceAsFirstConstructorArgumentWithDeprecationNotice(): void
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->toArray()->willReturn([]);

        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager($config->reveal());
        restore_error_handler();

        $this->assertSame($pluginManager, $pluginManager->getCreationContext());
    }

    public function invalidConstructorArguments(): array
    {
        return [
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'string'     => ['invalid'],
            'array'      => [['invokables' => []]],
            'object'     => [(object) ['invokables' => []]],
        ];
    }

    /**
     * @group migration
     * @param mixed $arg
     * @dataProvider invalidConstructorArguments
     */
    public function testPassingNonContainerNonConfigNonNullFirstConstructorArgumentRaisesException($arg): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TestAsset\LenientPluginManager($arg);
    }

    /**
     * @group migration
     */
    public function testPassingConfigInstanceAsFirstConstructorArgumentSkipsSecondArgumentWithDeprecationNotice(): void
    {
        $config = $this->prophesize(ConfigInterface::class);
        $config->toArray()->willReturn(['services' => [self::class => $this]]);

        set_error_handler(function ($errno, $errstr) {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager($config->reveal(), ['services' => [self::class => []]]);
        restore_error_handler();

        $this->assertSame($this, $pluginManager->get(self::class));
    }

    /**
     * @group migration
     * @group autoinvokable
     */
    public function testAutoInvokableServicesAreNotKnownBeforeRetrieval(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());
        $this->assertFalse($pluginManager->has(InvokableObject::class));
    }

    /**
     * @group migration
     * @group autoinvokable
     */
    public function testSupportsRetrievingAutoInvokableServicesByDefault(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());
        $invokable     = $pluginManager->get(InvokableObject::class);
        $this->assertInstanceOf(InvokableObject::class, $invokable);
    }

    /**
     * @group migration
     * @group autoinvokable
     */
    public function testPluginManagersMayOptOutOfSupportingAutoInvokableServices(): void
    {
        $pluginManager = new TestAsset\NonAutoInvokablePluginManager(new ServiceManager());
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage(TestAsset\NonAutoInvokablePluginManager::class);
        $pluginManager->get(InvokableObject::class);
    }

    /**
     * @group migration
     */
    public function testValidateWillFallBackToValidatePluginWhenDefinedAndEmitDeprecationNotice(): void
    {
        $assertionCalled          = false;
        $instance                 = (object) [];
        $assertion                = function ($plugin) use ($instance, &$assertionCalled) {
            $this->assertSame($instance, $plugin);
            $assertionCalled = true;
        };
        $pluginManager            = new TestAsset\V2ValidationPluginManager(new ServiceManager());
        $pluginManager->assertion = $assertion;

        $errorHandlerCalled = false;
        set_error_handler(function (int $errno, string $errmsg) use (&$errorHandlerCalled): bool {
            $this->assertEquals(E_USER_DEPRECATED, $errno);
            $this->assertStringContainsString('3.0', $errmsg);
            $errorHandlerCalled = true;
            return true;
        }, E_USER_DEPRECATED);

        $pluginManager->validate($instance);
        restore_error_handler();

        $this->assertTrue($assertionCalled, 'Assertion was not called by validatePlugin!');
        $this->assertTrue($errorHandlerCalled, 'Error handler was not triggered by validatePlugin!');
    }

    public function testSetServiceShouldRaiseExceptionForInvalidPlugin(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());
        $this->expectException(InvalidServiceException::class);
        $pluginManager->setService(stdClass::class, new stdClass());
    }

    public function testPassingServiceInstanceViaConfigureShouldRaiseExceptionForInvalidPlugin(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());
        $this->expectException(InvalidServiceException::class);
        $pluginManager->configure([
            'services' => [
                stdClass::class => new stdClass(),
            ],
        ]);
    }

    /**
     * @group 79
     * @group 78
     */
    public function testAbstractFactoryGetsCreationContext(): void
    {
        $serviceManager  = new ServiceManager();
        $pluginManager   = new SimplePluginManager($serviceManager);
        $abstractFactory = $this->prophesize(AbstractFactoryInterface::class);
        $abstractFactory->canCreate($serviceManager, 'foo')
            ->willReturn(true);
        $abstractFactory->__invoke($serviceManager, 'foo', null)
            ->willReturn(new InvokableObject());
        $pluginManager->addAbstractFactory($abstractFactory->reveal());
        $this->assertInstanceOf(InvokableObject::class, $pluginManager->get('foo'));
    }

    public function testAliasPropertyResolves(): void
    {
        $pluginManager = new V2v3PluginManager(new ServiceManager());
        $this->assertInstanceOf(InvokableObject::class, $pluginManager->get('foo'));
    }
}
