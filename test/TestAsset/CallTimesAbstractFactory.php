<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

final class CallTimesAbstractFactory implements AbstractFactoryInterface
{
    protected static int $callTimes = 0;

    /** {@inheritDoc} */
    public function canCreate(ContainerInterface $container, $name)
    {
        self::$callTimes++;

        return false;
    }

    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $className, ?array $options = null)
    {
    }

    public static function getCallTimes(): int
    {
        return self::$callTimes;
    }

    public static function setCallTimes(int $callTimes): void
    {
        self::$callTimes = $callTimes;
    }
}
