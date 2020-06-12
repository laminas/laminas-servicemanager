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
        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    public function testConfigurationCanBeMerged()
    {
        $serviceManager = new SimpleServiceManager([
            'factories' => [
                DateTime::class => InvokableFactory::class
            ]
        ]);

        self::assertTrue($serviceManager->has(DateTime::class));
        // stdClass service is inlined in SimpleServiceManager
        self::assertTrue($serviceManager->has(stdClass::class));
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
        self::assertTrue(isset($instance->option), 'Delegator-injected option was not found');
        self::assertEquals(
            $config['option'],
            $instance->option,
            'Delegator-injected option does not match configuration'
        );
        self::assertEquals('bar', $instance->foo);
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
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: T' => [ $sharedByDefault,  $serviceShared,  $serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: T, ServiceIsDefined: F' => [ $sharedByDefault,  $serviceShared, !$serviceDefined,  $shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: T' => [ $sharedByDefault, !$serviceShared,  $serviceDefined, !$shouldReturnSameInstance],
            'SharedByDefault: T, ServiceIsExplicitlyShared: F, ServiceIsDefined: F' => [ $sharedByDefault, !$serviceShared, !$serviceDefined,  $shouldReturnSameInstance],
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

        self::assertEquals($shouldBeSameInstance, $a === $b);
    }

    public function testMapsOneToOneInvokablesAsInvokableFactoriesInternally()
    {
        $config = [
            'invokables' => [
                InvokableObject::class => InvokableObject::class,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        self::assertAttributeSame([
            InvokableObject::class => InvokableFactory::class,
        ], 'factories', $serviceManager, 'Invokable object factory not found');
    }

    public function testMapsNonSymmetricInvokablesAsAliasPlusInvokableFactory()
    {
        $config = [
            'invokables' => [
                'Invokable' => InvokableObject::class,
            ],
        ];

        $serviceManager = new ServiceManager($config);
        self::assertAttributeSame([
            'Invokable' => InvokableObject::class,
        ], 'aliases', $serviceManager, 'Alias not found for non-symmetric invokable');
        self::assertAttributeSame([
            InvokableObject::class => InvokableFactory::class,
        ], 'factories', $serviceManager, 'Factory not found for non-symmetric invokable target');
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

        self::assertNotSame($instance1, $instance2);
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

        self::assertNotSame($instance1, $instance2);
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

        self::assertSame($service, $alias);
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

        self::assertSame($service, $alias);
        self::assertSame($service, $headAlias);
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
                [ $this->anything(), $this->equalTo('Alias') ],
                [ $this->anything(), $this->equalTo('ServiceName')]
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
            'aliases'            => [
                'Alias' => 'ServiceName',
            ],
            'abstract_factories' => [
                $abstractFactory,
            ],
        ]);

        $abstractFactory
            ->expects(self::any())
            ->method('canCreate')
            ->withConsecutive(
                [self::anything(), 'Alias'],
                [self::anything(), 'ServiceName']
            )
            ->will(self::returnCallback(function ($context, $name) {
                return $name === 'ServiceName';
            }));

        self::assertTrue($serviceManager->has('Alias'));
    }

    public function testResolvedAliasNoMatchingAbstractFactoryReturnsFalse()
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
            ->expects(self::any())
            ->method('canCreate')
            ->withConsecutive(
                [self::anything(), 'Alias'],
                [self::anything(), 'ServiceName']
            )
            ->willReturn(false);

        self::assertFalse($serviceManager->has('Alias'));
    }

    /**
     * @group #3
     * @see https://github.com/laminas/laminas-servicemanager/issues/3
     */
    public function testConfiguringADelegatorMultipleTimesDoesNotLeadToDuplicateDelegatorCalls()
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

        self::assertInstanceOf(InvokableObject::class, $instance);
        self::assertSame(1, $instance->getOptions()['inc']);
    }
}
