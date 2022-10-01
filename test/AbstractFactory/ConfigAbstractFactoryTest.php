<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory;

use ArrayObject;
use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SecondComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory
 */
final class ConfigAbstractFactoryTest extends TestCase
{
    private ConfigAbstractFactory $abstractFactory;

    private ServiceManager $serviceManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractFactory = new ConfigAbstractFactory();
        $this->serviceManager  = new ServiceManager();
    }

    public function testCanCreateReturnsTrueIfDependencyNotArrays(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => 'Blancmange',
            ]
        );

        self::assertFalse($this->abstractFactory->canCreate($this->serviceManager, InvokableObject::class));

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => 42,
                ],
            ]
        );

        self::assertTrue($this->abstractFactory->canCreate($this->serviceManager, InvokableObject::class));

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [
                        'Jabba',
                        'Gandalf',
                        'Blofeld',
                        42,
                    ],
                ],
            ]
        );

        self::assertTrue($this->abstractFactory->canCreate($this->serviceManager, InvokableObject::class));
    }

    public function testCanCreate(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ]
        );

        self::assertTrue($this->abstractFactory->canCreate($this->serviceManager, InvokableObject::class));
        self::assertFalse($this->abstractFactory->canCreate($this->serviceManager, ServiceManager::class));
    }

    public function testCanCreateReturnsTrueWhenConfigIsAnArrayObject(): void
    {
        $this->serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ])
        );

        self::assertTrue($this->abstractFactory->canCreate($this->serviceManager, InvokableObject::class));
        self::assertFalse($this->abstractFactory->canCreate($this->serviceManager, ServiceManager::class));
    }

    public function testFactoryCanCreateInstancesWhenConfigIsAnArrayObject(): void
    {
        $this->serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ])
        );

        self::assertInstanceOf(
            InvokableObject::class,
            $this->abstractFactory->__invoke($this->serviceManager, InvokableObject::class),
        );
    }

    public function testInvokeWithInvokableClass(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ]
        );

        self::assertInstanceOf(
            InvokableObject::class,
            $this->abstractFactory->__invoke($this->serviceManager, InvokableObject::class),
        );
    }

    public function testInvokeWithSimpleArguments(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class        => [],
                    SimpleDependencyObject::class => [
                        InvokableObject::class,
                    ],
                ],
            ]
        );
        $this->serviceManager->addAbstractFactory($this->abstractFactory);

        self::assertInstanceOf(
            SimpleDependencyObject::class,
            $this->abstractFactory->__invoke($this->serviceManager, SimpleDependencyObject::class)
        );
    }

    public function testInvokeWithComplexArguments(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class               => [],
                    SimpleDependencyObject::class        => [
                        InvokableObject::class,
                    ],
                    ComplexDependencyObject::class       => [
                        SimpleDependencyObject::class,
                        SecondComplexDependencyObject::class,
                    ],
                    SecondComplexDependencyObject::class => [
                        InvokableObject::class,
                    ],
                ],
            ]
        );
        $this->serviceManager->addAbstractFactory($this->abstractFactory);

        self::assertInstanceOf(
            ComplexDependencyObject::class,
            $this->abstractFactory->__invoke($this->serviceManager, ComplexDependencyObject::class)
        );
    }

    public function testExceptsWhenConfigNotSet(): void
    {
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a config array in the container');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigKeyNotSet(): void
    {
        $this->serviceManager->setService('config', []);
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a `' . ConfigAbstractFactory::class . '` key in the config array');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigIsNotArray(): void
    {
        $this->serviceManager->setService('config', 'Holistic');
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Config must be an array');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigIsNotArray(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => 'Detective_Agency',
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigDoesNotExist(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [],
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArray(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    'DirkGently' => 'Holistic',
                ],
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $this->abstractFactory->__invoke($this->serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArrayOfStrings(): void
    {
        $this->serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    'DirkGently' => [
                        'Holistic',
                        'Detective',
                        'Agency',
                        42,
                    ],
                ],
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            'Service dependencies config must be an array of strings, ["string","string","string","integer"] given'
        );

        $this->abstractFactory->__invoke($this->serviceManager, 'DirkGently');
    }
}
