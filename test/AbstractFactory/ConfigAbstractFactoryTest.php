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

    public function testCanCreateReturnsTrueIfDependencyNotArrays()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
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
                ]
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
                        42
                    ],
                ]
            ]
        );
        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
    }

    public function testCanCreate()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ]
            ]
        );

        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
        $this->assertFalse($abstractFactory->canCreate($serviceManager, ServiceManager::class));
    }

    public function testCanCreateReturnsTrueWhenConfigIsAnArrayObject()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ]
            ])
        );

        $this->assertTrue($abstractFactory->canCreate($serviceManager, InvokableObject::class));
        $this->assertFalse($abstractFactory->canCreate($serviceManager, ServiceManager::class));
    }

    public function testFactoryCanCreateInstancesWhenConfigIsAnArrayObject()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            new ArrayObject([
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ]
            ])
        );

        $this->assertInstanceOf(InvokableObject::class, $abstractFactory($serviceManager, InvokableObject::class));
    }

    public function testInvokeWithInvokableClass()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                ]
            ]
        );

        $this->assertInstanceOf(InvokableObject::class, $abstractFactory($serviceManager, InvokableObject::class));
    }

    public function testInvokeWithSimpleArguments()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                    SimpleDependencyObject::class => [
                        InvokableObject::class,
                    ],
                ]
            ]
        );
        $serviceManager->addAbstractFactory($abstractFactory);

        $this->assertInstanceOf(
            SimpleDependencyObject::class,
            $abstractFactory($serviceManager, SimpleDependencyObject::class)
        );
    }

    public function testInvokeWithComplexArguments()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    InvokableObject::class => [],
                    SimpleDependencyObject::class => [
                        InvokableObject::class,
                    ],
                    ComplexDependencyObject::class => [
                        SimpleDependencyObject::class,
                        SecondComplexDependencyObject::class,
                    ],
                    SecondComplexDependencyObject::class => [
                        InvokableObject::class,
                    ],
                ]
            ]
        );
        $serviceManager->addAbstractFactory($abstractFactory);

        $this->assertInstanceOf(
            ComplexDependencyObject::class,
            $abstractFactory($serviceManager, ComplexDependencyObject::class)
        );
    }

    public function testExceptsWhenConfigNotSet()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a config array in the container');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigKeyNotSet()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', []);
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Cannot find a `' . ConfigAbstractFactory::class . '` key in the config array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenConfigIsNotArray()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', 'Holistic');
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Config must be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigIsNotArray()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => 'Detective_Agency'
            ]
        );
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Service dependencies config must exist and be an array');

        $abstractFactory($serviceManager, 'Dirk_Gently');
    }

    public function testExceptsWhenServiceConfigDoesNotExist()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
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

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArray()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
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

    public function testExceptsWhenServiceConfigForRequestedNameIsNotArrayOfStrings()
    {
        $abstractFactory = new ConfigAbstractFactory();
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            [
                ConfigAbstractFactory::class => [
                    'DirkGently' => [
                        'Holistic',
                        'Detective',
                        'Agency',
                        42
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
