<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Factory;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\Factory\InvokableFactory
 */
final class InvokableFactoryTest extends TestCase
{
    public function testCanCreateObject(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new InvokableFactory();

        $object = $factory($container, InvokableObject::class, ['foo' => 'bar']);

        self::assertInstanceOf(InvokableObject::class, $object);
        self::assertEquals(['foo' => 'bar'], $object->options);
    }
}
