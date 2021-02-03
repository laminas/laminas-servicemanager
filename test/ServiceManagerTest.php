<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use DateTime;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleServiceManager;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;


use function get_class;

/**
 * @covers \Laminas\ServiceManager\ServiceManager
 */
class ServiceManagerTest extends TestCase
{
    use CommonServiceLocatorBehaviorsTrait;

    public function createContainer(array $config = [])
    {
        $this->creationContext = new ServiceManager($config);
        return $this->creationContext;
    }

    public function testServiceManagerIsAPsr11Container()
    {
        $container = $this->createContainer();
        $this->assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testConfigurationCanBeMerged()
    {
        $serviceManager = new SimpleServiceManager([
            'factories' => [
                DateTime::class => InvokableFactory::class
            ]
        ]);

        $this->assertTrue($serviceManager->has(DateTime::class));
        // stdClass service is inlined in SimpleServiceManager
        $this->assertTrue($serviceManager->has(stdClass::class));
    }

    public function testConfigurationTakesPrecedenceWhenMerged()
    {
        $factory = $this->getMockBuilder(FactoryInterface::class)
            ->getMock();

        $factory->expects($this->once())->method('__invoke');

        $serviceManager = new SimpleServiceManager([
            'factories' => [
                stdClass::class => $factory
            ]
        ]);

        $serviceManager->get(stdClass::class);
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::doCreate
     * @covers \Laminas\ServiceManager\ServiceManager::createDelegatorFromName
     */
    public function testCanWrapCreationInDelegators()
    {
        $config = [
            'option' => 'OPTIONED',
        ];
        $serviceManager = new ServiceManager([
            'services'  => [
                'config' => $config,
            ],
            'factories' => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators' => [
                stdClass::class => [
                    TestAsset\PreDelegator::class,
                    function ($container, $name, $callback) {
                        $instance = $callback();
                        $instance->foo = 'bar';
                        return $instance;
                    },
                ],
            ],
        ]);

        $instance = $serviceManager->get(stdClass::class);
        $this->assertTrue(isset($instance->option), 'Delegator-injected option was not found');
        $this->assertEquals(
            $config['option'],
            $instance->option,
            'Delegator-injected option does not match configuration'
        );
        $this->assertEquals('bar', $instance->foo);
    }

    public function shareProvider()
    {
        $sharedByDefault          = true;
        $serviceShared            = true;
        $serviceDefined           = true;
        $shouldReturnSameInstance = true;

        // @codingStandardsIgnoreStart
        return [
            // Description => [$sharedByDefault, $serviceShared, $serviceDefined, $expectedInstance]
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: T' => [$sharedByDefault,  $serviceShared,  $serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: F' => [$sharedByDefault,  $serviceShared, !$serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: T' => [$sharedByDefault, !$serviceShared,  $serviceDefined, !$shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: F' => [$sharedByDefault, !$serviceShared, !$serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: F, ServiceIsExplicitlyShared: T, ServiceIsDefined: T' => [!$sharedByDefault,  $serviceShared,  $serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: F, ServiceIsExplicitlyShared: T, ServiceIsDefined: F' => [!$sharedByDefault,  $serviceShared, !$serviceDefined, !$shouldReturnSameInstance],
            'SharedByDefault: F, ServiceIsExplicitlyShared: F, ServiceIsDefined: T' => [!$sharedByDefault, !$serviceShared,  $serviceDefined, !$shouldReturnSameInstance],
            'SharedByDefault: F, ServiceIsExplicitlyShared: F, ServiceIsDefined: F' => [!$sharedByDefault, !$serviceShared, !$serviceDefined, !$shouldReturnSameInstance],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider shareProvider
     */
    public function testShareability($sharedByDefault, $serviceShared, $serviceDefined, $shouldBeSameInstance)
    {
        $config = [
            'shared_by_default' => $sharedByDefault,
            'factories'         => [
                stdClass::class => InvokableFactory::class,
            ]
        ];

        if ($serviceDefined) {
            $config['shared'] = [
                stdClass::class => $serviceShared
            ];
        }

        $serviceManager = new ServiceManager($config);

        $a = $serviceManager->get(stdClass::class);
        $b = $serviceManager->get(stdClass::class);

        $this->assertEquals($shouldBeSameInstance, $a === $b);
    }

    public function testMapsOneToOneInvokablesAsInvokableFactoriesInternally()
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

        $this->assertSame(
            [
                InvokableObject::class => InvokableFactory::class,
            ],
            $serviceManager->getFactories(),
            'Invokable object factory not found'
        );
    }

    public function testMapsNonSymmetricInvokablesAsAliasPlusInvokableFactory()
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

        $this->assertSame(
            [
                'Invokable' => InvokableObject::class,
            ],
            $serviceManager->getAliases(),
            'Alias not found for non-symmetric invokable'
        );

        $this->assertSame(
            [
                InvokableObject::class => InvokableFactory::class,
            ],
            $serviceManager->getFactories()
        );
    }

    /**
     * @depends testMapsNonSymmetricInvokablesAsAliasPlusInvokableFactory
     */
    public function testSharedServicesReferencingInvokableAliasShouldBeHonored()
    {
        $config = [
            'invokables' => [
                'Invokable' => InvokableObject::class,
            ],
            'shared' => [
                'Invokable' => false,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        $instance1 = $serviceManager->get('Invokable');
        $instance2 = $serviceManager->get('Invokable');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testSharedServicesReferencingAliasShouldBeHonored()
    {
        $config = [
            'aliases' => [
                'Invokable' => InvokableObject::class,
            ],
            'factories' => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'shared' => [
                'Invokable' => false,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        $instance1 = $serviceManager->get('Invokable');
        $instance2 = $serviceManager->get('Invokable');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testAliasToAnExplicitServiceShouldWork()
    {
        $config = [
            'aliases' => [
                'Invokable' => InvokableObject::class,
            ],
            'services' => [
                InvokableObject::class => new InvokableObject(),
            ],
        ];

        $serviceManager = new ServiceManager($config);

        $service = $serviceManager->get(InvokableObject::class);
        $alias   = $serviceManager->get('Invokable');

        $this->assertSame($service, $alias);
    }

    /**
     * @depends testAliasToAnExplicitServiceShouldWork
     */
    public function testSetAliasShouldWorkWithRecursiveAlias()
    {
        $config = [
            'aliases' => [
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

        $this->assertSame($service, $alias);
        $this->assertSame($service, $headAlias);
    }

    public function testAbstractFactoryShouldBeCheckedForResolvedAliasesInsteadOfAliasName()
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases' => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->method('canCreate')
            ->withConsecutive(
                [$this->anything(), $this->equalTo('Alias')],
                [$this->anything(), $this->equalTo('ServiceName')]
            )
            ->willReturnCallback(function ($context, $name) {
                return $name === 'Alias';
            });
        $this->assertTrue($serviceManager->has('Alias'));
    }

    public static function sampleFactory()
    {
        return new stdClass();
    }

    public function testFactoryMayBeStaticMethodDescribedByCallableString()
    {
        $config = [
            'factories' => [
                stdClass::class => 'LaminasTest\ServiceManager\ServiceManagerTest::sampleFactory',
            ]
        ];
        $serviceManager = new SimpleServiceManager($config);
        $this->assertEquals(stdClass::class, get_class($serviceManager->get(stdClass::class)));
    }

    public function testResolvedAliasFromAbstractFactory()
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases' => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects($this->any())
            ->method('canCreate')
            ->withConsecutive(
                [$this->anything(), 'Alias'],
                [$this->anything(), 'ServiceName']
            )
            ->will($this->returnCallback(function ($context, $name) {
                return $name === 'ServiceName';
            }));

        $this->assertTrue($serviceManager->has('Alias'));
    }

    public function testResolvedAliasNoMatchingAbstractFactoryReturnsFalse()
    {
        $abstractFactory = $this->createMock(AbstractFactoryInterface::class);

        $serviceManager = new SimpleServiceManager([
            'aliases' => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects($this->any())
            ->method('canCreate')
            ->withConsecutive(
                [$this->anything(), 'Alias'],
                [$this->anything(), 'ServiceName']
            )
            ->willReturn(false);

        $this->assertFalse($serviceManager->has('Alias'));
    }

    /**
     * Hotfix #3
     * @see https://github.com/laminas/laminas-servicemanager/issues/3
     */
    public function testConfigureMultipleTimesAvoidsDuplicates()
    {
        $delegatorFactory = function (
            ContainerInterface $container,
            $name,
            callable $callback
        ) {
            /** @var InvokableObject $instance */
            $instance = $callback();
            $options = $instance->getOptions();
            $inc = $options['inc'] ?? 0;
            return new InvokableObject(['inc' => ++$inc]);
        };

        $config = [
            'factories' => [
                'Foo' => function () {
                    return new InvokableObject();
                },
            ],
            'delegators' => [
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

        $this->assertInstanceOf(InvokableObject::class, $instance);
        $this->assertSame(1, $instance->getOptions()['inc']);
    }

    /**
     * @link https://github.com/laminas/laminas-servicemanager/issues/70
     */
    public function testWillApplyAllInitializersAfterServiceCreation(): void
    {
        $initializerOneCalled = $initializerTwoCalled = false;
        $initializers = [
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
            'invokables' => [
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
     * @param non-empty-string $serviceName
     * @param non-empty-string $alias
     * @dataProvider aliasedServices
     */
    public function testWontShareServiceWhenRequestedByAlias(array $config, string $serviceName, string $alias): void
    {
        $serviceManager = new ServiceManager($config);
        $service = $serviceManager->get($serviceName);
        $serviceFromAlias = $serviceManager->get($alias);
        $serviceFromServiceNameAfterUsingAlias = $serviceManager->get($serviceName);

        self::assertNotSame($service, $serviceFromAlias);
        self::assertNotSame($service, $serviceFromServiceNameAfterUsingAlias);
        self::assertNotSame($serviceFromAlias, $serviceFromServiceNameAfterUsingAlias);
    }

    /**
     * @return array<non-empty-string,array{0:array<string,mixed>,1:non-empty-string,2:non-empty-string}>
     */
    public function aliasedServices(): array
    {
        return [
            'invokables' => [
                [
                    'invokables' => [
                        stdClass::class => stdClass::class,
                    ],
                    'aliases' => [
                        'object' => stdClass::class,
                    ],
                    'shared' => [
                        stdClass::class => false,
                    ],
                ],
                stdClass::class,
                'object',
            ],
            'factories' => [
                [
                    'factories' => [
                        stdClass::class => static function (): stdClass {
                            return new stdClass();
                        },
                    ],
                    'aliases' => [
                        'object' => stdClass::class,
                    ],
                    'shared' => [
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

                            public function canCreate(\Interop\Container\ContainerInterface $container, $requestedName)
                            {
                                return $requestedName === stdClass::class;
                            }

                            public function __invoke(
                                \Interop\Container\ContainerInterface $container,
                                $requestedName,
                                array $options = null
                            ) {
                                return new stdClass();
                            }
                        }
                    ],
                    'aliases' => [
                        'object' => stdClass::class,
                    ],
                    'shared' => [
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
            'services' => [
                'Config' => [],
            ],
            'aliases' => [
                'config' => 'Config',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        self::assertTrue($serviceManager->has('config'));
    }
}
