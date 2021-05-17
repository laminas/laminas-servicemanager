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

class ConfigAbstractFactoryTest extends TestCase
{
    public function testCanCreateReturnsTrueIfDependencyNotArrays(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => 'Blancmange',
            ]
        );

        $this->assertFalse($abstractFactory->canCreate($serviceManager, InvokableObject::class));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => 42,
                ],
            ]
        );
        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
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
        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
    }

    public function testCanCreate(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ]
        );

        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
        $this->assertFalse($abstractFactory->canCreate($serviceManager, ServiceManager::class));
    }

    public function testCanCreateReturnsTrueWhenConfigIsAnArrayObject(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ])
        );

        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
        $this->assertFalse($abstractFactory->canCreate($serviceManager, ServiceManager::class));
    }

    public function testFactoryCanCreateInstancesWhenConfigIsAnArrayObject(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ])
        );

        $this->assertInstanceOf(InvokableObject::class, $abstractFactory($serviceManager, InvokableObject::class));
    }

    public function testInvokeWithInvokableClass(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ],
            ]
        );

        $this->assertInstanceOf(InvokableObject::class, $abstractFactory($serviceManager, InvokableObject::class));
    }

    public function testInvokeWithSimpleArguments(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
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
        $serviceManager->addAbstractFactory($abstractFactory);

        $this->assertInstanceOf(
            SimpleDependencyObject::class,
            $abstractFactory($serviceManager, SimpleDependencyObject::class)
        );
    }

    public function testInvokeWithComplexArguments(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
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
        $serviceManager->addAbstractFactory($abstractFactory);

        $this->assertInstanceOf(
            ComplexDependencyObject::class,
            $abstractFactory($serviceManager, ComplexDependencyObject::class)
        );
    }

    public function testExceptsWhenConfigNotSet(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a config array in the container');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigKeyNotSet(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService('config', []);
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a `' . ConfigAbstractFactory::class . '` key in the config array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigIsNotArray(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService('config', 'Holistic');
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Config must be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigIsNotArray(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => 'Detective_Agency',
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigDoesNotExist(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [],
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArray(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    'DirkGently' => 'Holistic',
                ],
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArrayOfStrings(): void
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager  = new ServiceManager();
        $serviceManager->setService(
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

        $abstractFactory($serviceManager, 'DirkGently');
    }
}
