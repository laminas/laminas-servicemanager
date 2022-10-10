<?php // phpcs:disable Generic.Files.LineLength.TooLong


declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimplePluginManager;
use LaminasTest\ServiceManager\TestAsset\V2v3PluginManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

/**
 * @covers \Laminas\ServiceManager\AbstractPluginManager
 */
final class AbstractPluginManagerTest extends TestCase
{
    use CommonServiceLocatorBehaviorsTrait;

    public function createContainer(array $config = []): ServiceManager
    {
        $this->creationContext = new ServiceManager();
        return new TestAsset\LenientPluginManager($this->creationContext, $config);
    }

    public function testInjectCreationContextInFactories(): void
    {
        $invokableFactory = $this->createMock(FactoryInterface::class);

        $config = [
            'factories' => [
                InvokableObject::class => $invokableFactory,
            ],
        ];

        $container     = $this->createMock(ContainerInterface::class);
        $pluginManager = new SimplePluginManager($container, $config);

        $invokableFactory
            ->expects(self::once())
            ->method('__invoke')
            ->with($container, InvokableObject::class)
            ->will(self::returnValue(new InvokableObject()));

        $object = $pluginManager->get(InvokableObject::class);

        self::assertInstanceOf(InvokableObject::class, $object);
    }

    public function testValidateInstance(): void
    {
        $config = [
            'factories' => [
                InvokableObject::class => new InvokableFactory(),
                stdClass::class        => new InvokableFactory(),
            ],
        ];

        $container     = $this->createMock(ContainerInterface::class);
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

        $container     = $this->createMock(ContainerInterface::class);
        $pluginManager = new SimplePluginManager($container, $config);

        $first  = $pluginManager->get(InvokableObject::class);
        $second = $pluginManager->get(InvokableObject::class);

        self::assertInstanceOf(InvokableObject::class, $first);
        self::assertInstanceOf(InvokableObject::class, $second);
        self::assertSame($first, $second);
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

        $container     = $this->createMock(ContainerInterface::class);
        $pluginManager = new SimplePluginManager($container, $config);

        $first  = $pluginManager->get(InvokableObject::class, $options);
        $second = $pluginManager->get(InvokableObject::class, $options);

        self::assertInstanceOf(InvokableObject::class, $first);
        self::assertInstanceOf(InvokableObject::class, $second);
        self::assertNotSame($first, $second);
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
                    static function ($container, $name, $callback) {
                        $instance      = $callback();
                        $instance->foo = 'bar';

                        return $instance;
                    },
                ],
            ],
        ]);

        $instance = $pluginManager->get(stdClass::class);

        self::assertInstanceOf(stdClass::class, $instance);
        self::assertTrue(isset($instance->option), 'Delegator-injected option was not found');
        self::assertEquals(
            $config['option'],
            $instance->option,
            'Delegator-injected option does not match configuration'
        );
        self::assertEquals('bar', $instance->foo);
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
        $this->expectExceptionMessage($pluginManager::class);
        $pluginManager->get('Some\Unknown\Service');
    }

    /**
     * @group migration
     */
    public function testCallingSetServiceLocatorSetsCreationContextWithDeprecationNotice(): void
    {
        set_error_handler(static function ($errno, $errstr): void {
            self::assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager();
        restore_error_handler();

        self::assertSame($pluginManager, $pluginManager->getCreationContext());
        $serviceManager = new ServiceManager();

        set_error_handler(static function ($errno, $errstr): void {
            self::assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager->setServiceLocator($serviceManager);
        restore_error_handler();

        self::assertSame($serviceManager, $pluginManager->getCreationContext());
    }

    /**
     * @group migration
     */
    public function testPassingNoInitialConstructorArgumentSetsPluginManagerAsCreationContextWithDeprecationNotice(): void
    {
        set_error_handler(static function ($errno, $errstr): void {
            self::assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager();
        restore_error_handler();

        self::assertSame($pluginManager, $pluginManager->getCreationContext());
    }

    /**
     * @group migration
     */
    public function testCanPassConfigInterfaceAsFirstConstructorArgumentWithDeprecationNotice(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config
            ->expects(self::once())
            ->method('toArray')
            ->willReturn([]);

        set_error_handler(static function ($errno, $errstr): void {
            self::assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager($config);
        restore_error_handler();

        self::assertSame($pluginManager, $pluginManager->getCreationContext());
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
     * @dataProvider invalidConstructorArguments
     */
    public function testPassingNonContainerNonConfigNonNullFirstConstructorArgumentRaisesException(mixed $arg): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TestAsset\LenientPluginManager($arg);
    }

    /**
     * @group migration
     */
    public function testPassingConfigInstanceAsFirstConstructorArgumentSkipsSecondArgumentWithDeprecationNotice(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config
            ->expects(self::once())
            ->method('toArray')
            ->willReturn(['services' => [self::class => $this]]);

        set_error_handler(static function ($errno, $errstr): void {
            self::assertEquals(E_USER_DEPRECATED, $errno);
        }, E_USER_DEPRECATED);
        $pluginManager = new TestAsset\LenientPluginManager($config, ['services' => [self::class => []]]);
        restore_error_handler();

        self::assertSame($this, $pluginManager->get(self::class));
    }

    /**
     * @group migration
     * @group autoinvokable
     */
    public function testAutoInvokableServicesAreNotKnownBeforeRetrieval(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());

        self::assertFalse($pluginManager->has(InvokableObject::class));
    }

    /**
     * @group migration
     * @group autoinvokable
     */
    public function testSupportsRetrievingAutoInvokableServicesByDefault(): void
    {
        $pluginManager = new SimplePluginManager(new ServiceManager());
        $invokable     = $pluginManager->get(InvokableObject::class);

        self::assertInstanceOf(InvokableObject::class, $invokable);
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
        $assertion                = static function ($plugin) use ($instance, &$assertionCalled): void {
            self::assertSame($instance, $plugin);
            $assertionCalled = true;
        };
        $pluginManager            = new TestAsset\V2ValidationPluginManager(new ServiceManager());
        $pluginManager->assertion = $assertion;

        $errorHandlerCalled = false;
        set_error_handler(static function (int $errno, string $errmsg) use (&$errorHandlerCalled): bool {
            self::assertEquals(E_USER_DEPRECATED, $errno);
            self::assertStringContainsString('3.0', $errmsg);
            $errorHandlerCalled = true;

            return true;
        }, E_USER_DEPRECATED);

        $pluginManager->validate($instance);
        restore_error_handler();

        self::assertTrue($assertionCalled, 'Assertion was not called by validatePlugin!');
        self::assertTrue($errorHandlerCalled, 'Error handler was not triggered by validatePlugin!');
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
        $serviceManager = new ServiceManager();
        $pluginManager  = new SimplePluginManager($serviceManager);

        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);
        $abstractFactory
            ->expects(self::exactly(2))
            ->method('canCreate')
            ->with($serviceManager, 'foo')
            ->willReturn(true);

        $abstractFactory
            ->expects(self::once())
            ->method('__invoke')
            ->with($serviceManager, 'foo', null)
            ->willReturn(new InvokableObject());

        $pluginManager->addAbstractFactory($abstractFactory);

        self::assertInstanceOf(InvokableObject::class, $pluginManager->get('foo'));
    }

    public function testAliasPropertyResolves(): void
    {
        $pluginManager = new V2v3PluginManager(new ServiceManager());

        self::assertInstanceOf(InvokableObject::class, $pluginManager->get('foo'));
    }
}
