<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest\TargetObject;
use LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest\TargetObjectDelegator;
use PHPUnit\Framework\TestCase;

final class DelegatorAndAliasBehaviorTest extends TestCase
{
    public function testThatADelegatorTargetingAServiceWillExecute(): void
    {
        $serviceManager = new ServiceManager([
            'factories'  => [
                TargetObject::class => InvokableFactory::class,
            ],
            'delegators' => [
                TargetObject::class => [
                    TargetObjectDelegator::class,
                ],
            ],
        ]);

        $service = $serviceManager->get(TargetObject::class);

        self::assertInstanceOf(TargetObject::class, $service);
        self::assertEquals(TargetObjectDelegator::DELEGATED_VALUE, $service->value);
    }

    public function testThatADelegatorWillNotExecuteWhenItTargetsAnAlias(): void
    {
        $serviceManager = new ServiceManager([
            'factories'  => [
                TargetObject::class => InvokableFactory::class,
            ],
            'aliases'    => [
                'Some Alias' => TargetObject::class,
            ],
            'delegators' => [
                'Some Alias' => [
                    TargetObjectDelegator::class,
                ],
            ],
        ]);

        $service = $serviceManager->get('Some Alias');

        self::assertInstanceOf(TargetObject::class, $service);
        self::assertEquals(TargetObject::INITIAL_VALUE, $service->value);
    }
}
