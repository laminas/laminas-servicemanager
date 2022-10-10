<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use DateTime;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * @see ConfigInterface
 *
 * @covers \Laminas\ServiceManager\ServiceManager
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
final class ServiceManagerTest extends TestCase
{
    use CommonServiceLocatorBehaviorsTrait;

    /**
     * @psalm-param ServiceManagerConfigurationType $config
     */
    public function createContainer(array $config = []): ServiceManager
    {
        $container             = new ServiceManager($config);
        $this->creationContext = $container;

        return $container;
    }

    public function testServiceManagerIsAPsr11Container(): void
    {
        $container = $this->createContainer();

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testConfigurationCanBeMerged(): void
    {
        $serviceManager = new SimpleServiceManager([
            'factories' => [
                DateTime::class => InvokableFactory::class,
            ],
        ]);

        self::assertTrue($serviceManager->has(DateTime::class));
        // stdClass service is inlined in SimpleServiceManager
        self::assertTrue($serviceManager->has(stdClass::class));
    }

    public function testConfigurationTakesPrecedenceWhenMerged(): void
    {
        $factory = $this->createMock(FactoryInterface::class);

        $service = new stdClass();
        $factory
            ->expects(self::once())
            ->method('__invoke')
            ->willReturn($service);

        $serviceManager = new SimpleServiceManager([
            'factories' => [
                stdClass::class => $factory,
            ],
        ]);

        $serviceFromServiceManager = $serviceManager->get(stdClass::class);

        self::assertSame($service, $serviceFromServiceManager);
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::doCreate
     * @covers \Laminas\ServiceManager\ServiceManager::createDelegatorFromName
     */
    public function testCanWrapCreationInDelegators(): void
    {
        $config         = [
            'option' => 'OPTIONED',
        ];
        $serviceManager = new ServiceManager([
            'services'   => [
                'config' => $config,
            ],
            'factories'  => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators' => [
                stdClass::class => [
                    TestAsset\PreDelegator::class,
                    static function (ContainerInterface $container, string $name, callable $callback): object {
                        $instance = $callback();
                        self::assertInstanceOf(stdClass::class, $instance);
                        $instance->foo = 'bar';

                        return $instance;
                    },
                ],
            ],
        ]);

        $instance = $serviceManager->get(stdClass::class);

        self::assertTrue(isset($instance->option), 'Delegator-injected option was not found');
        self::assertEquals(
            $config['option'],
            $instance->option,
            'Delegator-injected option does not match configuration'
        );
        self::assertEquals('bar', $instance->foo);
    }

    public function shareProvider(): array
    {
        $sharedByDefault          = true;
        $serviceShared            = true;
        $serviceDefined           = true;
        $shouldReturnSameInstance = true;

        return [
            // Description => [$sharedByDefault, $serviceShared, $serviceDefined, $expectedInstance]
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: T' => [
                $sharedByDefault,
                $serviceShared,
                $serviceDefined,
                $shouldReturnSameInstance,
            ],
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: F' => [
                $sharedByDefault,
                $serviceShared,
                ! $serviceDefined,
                $shouldReturnSameInstance,
            ],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: T' => [
                $sharedByDefault,
                ! $serviceShared,
                $serviceDefined,
                ! $shouldReturnSameInstance,
            ],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: F' => [
                $sharedByDefault,
                ! $serviceShared,
                ! $serviceDefined,
                $shouldReturnSameInstance,
            ],
            'SharedByDefault: F, ServiceIsExplicitlyShared: T, ServiceIsDefined: T' => [
                ! $sharedByDefault,
                $serviceShared,
                $serviceDefined,
                $shouldReturnSameInstance,
            ],
            'SharedByDefault: F, ServiceIsExplicitlyShared: T, ServiceIsDefined: F' => [
                ! $sharedByDefault,
                $serviceShared,
                ! $serviceDefined,
                ! $shouldReturnSameInstance,
            ],
            'SharedByDefault: F, ServiceIsExplicitlyShared: F, ServiceIsDefined: T' => [
                ! $sharedByDefault,
                ! $serviceShared,
                $serviceDefined,
                ! $shouldReturnSameInstance,
            ],
            'SharedByDefault: F, ServiceIsExplicitlyShared: F, ServiceIsDefined: F' => [
                ! $sharedByDefault,
                ! $serviceShared,
                ! $serviceDefined,
                ! $shouldReturnSameInstance,
            ],
        ];
    }

    /**
     * @dataProvider shareProvider
     */
    public function testShareability(
        bool $sharedByDefault,
        bool $serviceShared,
        bool $serviceDefined,
        bool $shouldBeSameInstance
    ): void {
        $config = [
            'shared_by_default' => $sharedByDefault,
            'factories'         => [
                stdClass::class => InvokableFactory::class,
            ],
        ];

        if ($serviceDefined) {
            $config['shared'] = [
                stdClass::class => $serviceShared,
            ];
        }

        $serviceManager = new ServiceManager($config);

        $a = $serviceManager->get(stdClass::class);
        $b = $serviceManager->get(stdClass::class);

        self::assertEquals($shouldBeSameInstance, $a === $b);
    }

    public function testMapsOneToOneInvokablesAsInvokableFactoriesInternally(): void
    {
        $config = [
            'invokables' => [
                InvokableObject::class => InvokableObject::class,
            ],
        ];

        $serviceManager = new class ($config) extends ServiceManager
        {
            public function getFactories(): array
            {
                return $this->factories;
            }
        };

        self::assertSame(
            [
                InvokableObject::class => InvokableFactory::class,
            ],
            $serviceManager->getFactories(),
            'Invokable object factory not found'
        );
    }

    public function testMapsNonSymmetricInvokablesAsAliasPlusInvokableFactory(): void
    {
        $config = [
            'invokables' => [
                'Invokable' => InvokableObject::class,
            ],
        ];

        $serviceManager = new class ($config) extends ServiceManager
        {
            public function getFactories(): array
            {
                return $this->factories;
            }

            public function getAliases(): array
            {
                return $this->aliases;
            }
        };

        self::assertSame(
            [
                'Invokable' => InvokableObject::class,
            ],
            $serviceManager->getAliases(),
            'Alias not found for non-symmetric invokable'
        );

        self::assertSame(
            [
                InvokableObject::class => InvokableFactory::class,
            ],
            $serviceManager->getFactories()
        );
    }

    /**
     * @depends testMapsNonSymmetricInvokablesAsAliasPlusInvokableFactory
     */
    public function testSharedServicesReferencingInvokableAliasShouldBeHonored(): void
    {
        $config = [
            'invokables' => [
                'Invokable' => InvokableObject::class,
            ],
            'shared'     => [
                'Invokable' => false,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        $instance1      = $serviceManager->get('Invokable');
        $instance2      = $serviceManager->get('Invokable');

        self::assertNotSame($instance1, $instance2);
    }

    public function testSharedServicesReferencingAliasShouldBeHonored(): void
    {
        $config = [
            'aliases'   => [
                'Invokable' => InvokableObject::class,
            ],
            'factories' => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'shared'    => [
                'Invokable' => false,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        $instance1      = $serviceManager->get('Invokable');
        $instance2      = $serviceManager->get('Invokable');

        self::assertNotSame($instance1, $instance2);
    }

    public function testAliasToAnExplicitServiceShouldWork(): void
    {
        $config = [
            'aliases'  => [
                'Invokable' => InvokableObject::class,
            ],
            'services' => [
                InvokableObject::class => new InvokableObject(),
            ],
        ];

        $serviceManager = new ServiceManager($config);

        $service = $serviceManager->get(InvokableObject::class);
        $alias   = $serviceManager->get('Invokable');

        self::assertSame($service, $alias);
    }

    /**
     * @depends testAliasToAnExplicitServiceShouldWork
     */
    public function testSetAliasShouldWorkWithRecursiveAlias(): void
    {
        $config         = [
            'aliases'  => [
                'Alias' => 'TailInvokable',
            ],
            'services' => [
                InvokableObject::class => new InvokableObject(),
            ],
        ];
        $serviceManager = new ServiceManager($config);
        $serviceManager->setAlias('HeadAlias', 'Alias');
        $serviceManager->setAlias('TailInvokable', InvokableObject::class);

        $service   = $serviceManager->get(InvokableObject::class);
        $alias     = $serviceManager->get('Alias');
        $headAlias = $serviceManager->get('HeadAlias');

        self::assertSame($service, $alias);
        self::assertSame($service, $headAlias);
    }

    public function testAbstractFactoryShouldBeCheckedForResolvedAliasesInsteadOfAliasName(): void
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases'            => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects(self::once())
            ->method('canCreate')
            ->withConsecutive(
                [self::anything(), self::equalTo('Alias')],
                [self::anything(), self::equalTo('ServiceName')]
            )
            ->willReturnCallback(static fn ($context, string $name): bool => $name === 'Alias');

        self::assertTrue($serviceManager->has('Alias'));
    }

    public static function sampleFactory(): object
    {
        return new stdClass();
    }

    public function testFactoryMayBeStaticMethodDescribedByCallableString(): void
    {
        $config         = [
            'factories' => [
                stdClass::class => 'LaminasTest\ServiceManager\ServiceManagerTest::sampleFactory',
            ],
        ];
        $serviceManager = new SimpleServiceManager($config);

        self::assertEquals(stdClass::class, $serviceManager->get(stdClass::class)::class);
    }

    public function testResolvedAliasFromAbstractFactory(): void
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases'            => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects(self::exactly(2))
            ->method('canCreate')
            ->withConsecutive(
                [self::anything(), 'Alias'],
                [self::anything(), 'ServiceName']
            )
            ->willReturnCallback(static fn ($context, string $name): bool => $name === 'ServiceName');

        self::assertTrue($serviceManager->has('Alias'));
    }

    public function testResolvedAliasNoMatchingAbstractFactoryReturnsFalse(): void
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases'            => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects(self::exactly(2))
            ->method('canCreate')
            ->withConsecutive(
                [self::anything(), 'Alias'],
                [self::anything(), 'ServiceName']
            )
            ->willReturn(false);

        self::assertFalse($serviceManager->has('Alias'));
    }

    /**
     * Hotfix #3
     *
     * @see https://github.com/laminas/laminas-servicemanager/issues/3
     */
    public function testConfigureMultipleTimesAvoidsDuplicates(): void
    {
        $delegatorFactory = static function (
            ContainerInterface $container,
            $name,
            callable $callback
        ): InvokableObject {
            /** @var InvokableObject $instance */
            $instance = $callback();
            $options  = $instance->getOptions();
            $inc      = $options['inc'] ?? 0;

            return new InvokableObject(['inc' => ++$inc]);
        };

        $config = [
            'factories'     => [
                'Foo' => static fn (): InvokableObject => new InvokableObject(),
            ],
            'delegators'    => [
                'Foo' => [
                    $delegatorFactory,
                    LazyServiceFactory::class,
                ],
            ],
            'lazy_services' => [
                'class_map' => [
                    'Foo' => InvokableObject::class,
                ],
            ],
        ];

        $serviceManager = new ServiceManager($config);
        $serviceManager->configure($config);

        /** @var InvokableObject $instance */
        $instance = $serviceManager->get('Foo');

        self::assertInstanceOf(InvokableObject::class, $instance);
        self::assertSame(1, $instance->getOptions()['inc']);
    }

    /**
     * @link https://github.com/laminas/laminas-servicemanager/issues/70
     */
    public function testWillApplyAllInitializersAfterServiceCreation(): void
    {
        $initializerOneCalled = $initializerTwoCalled = false;
        $initializers         = [
            static function (object $service) use (&$initializerOneCalled): object {
                $initializerOneCalled = true;

                return $service;
            },
            static function (object $service) use (&$initializerTwoCalled): object {
                $initializerTwoCalled = true;

                return $service;
            },
        ];

        $serviceManager = new ServiceManager([
            'invokables'   => [
                stdClass::class => stdClass::class,
            ],
            'initializers' => $initializers,
        ]);

        $serviceManager->get(stdClass::class);

        self::assertTrue($initializerOneCalled, 'First initializer was not called');
        self::assertTrue($initializerTwoCalled, 'Second initializer was not called');
    }

    /**
     * @param array<string,mixed>  $config
     * @psalm-param ServiceManagerConfigurationType $config
     * @param non-empty-string $serviceName
     * @param non-empty-string $alias
     * @dataProvider aliasedServices
     */
    public function testWontShareServiceWhenRequestedByAlias(array $config, string $serviceName, string $alias): void
    {
        $serviceManager                        = new ServiceManager($config);
        $service                               = $serviceManager->get($serviceName);
        $serviceFromAlias                      = $serviceManager->get($alias);
        $serviceFromServiceNameAfterUsingAlias = $serviceManager->get($serviceName);

        self::assertNotSame($service, $serviceFromAlias);
        self::assertNotSame($service, $serviceFromServiceNameAfterUsingAlias);
        self::assertNotSame($serviceFromAlias, $serviceFromServiceNameAfterUsingAlias);
    }

    /**
     * @psalm-return array<non-empty-string,array{
     *     0:ServiceManagerConfigurationType,
     *     1:non-empty-string,
     *     2:non-empty-string
     * }>
     */
    public function aliasedServices(): array
    {
        return [
            'invokables'         => [
                [
                    'invokables' => [
                        stdClass::class => stdClass::class,
                    ],
                    'aliases'    => [
                        'object' => stdClass::class,
                    ],
                    'shared'     => [
                        stdClass::class => false,
                    ],
                ],
                stdClass::class,
                'object',
            ],
            'factories'          => [
                [
                    'factories' => [
                        stdClass::class => static fn (): stdClass => new stdClass(),
                    ],
                    'aliases'   => [
                        'object' => stdClass::class,
                    ],
                    'shared'    => [
                        stdClass::class => false,
                    ],
                ],
                stdClass::class,
                'object',
            ],
            'abstract factories' => [
                [
                    'abstract_factories' => [
                        new class implements AbstractFactoryInterface {
                            /**
                             * @param string $requestedName
                             */
                            public function canCreate(ContainerInterface $container, $requestedName): bool
                            {
                                return $requestedName === stdClass::class;
                            }

                            /**
                             * @param string $requestedName
                             */
                            public function __invoke(
                                ContainerInterface $container,
                                $requestedName,
                                ?array $options = null
                            ): object {
                                return new stdClass();
                            }
                        },
                    ],
                    'aliases'            => [
                        'object' => stdClass::class,
                    ],
                    'shared'             => [
                        stdClass::class => false,
                    ],
                ],
                stdClass::class,
                'object',
            ],
        ];
    }

    /**
     * @link https://github.com/laminas/laminas-servicemanager/issues/80
     */
    public function testHasVerifiesAliasesBeforeUsingAbstractFactories(): void
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);
        $abstractFactory
            ->expects(self::never())
            ->method('canCreate');

        $serviceManager = new ServiceManager([
            'services'           => [
                'Config' => [],
            ],
            'aliases'            => [
                'config' => 'Config',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        self::assertTrue($serviceManager->has('config'));
    }
}
