<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class CallTimesAbstractFactory implements AbstractFactoryInterface
{
    /** @var int */
    protected static $callTimes = 0;

    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $name)
    {
        self::$callTimes++;

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $className, ?array $options = null)
    {
    }

    /**
     * @return int
     */
    public static function getCallTimes()
    {
        return self::$callTimes;
    }

    /**
     * @param int $callTimes
     */
    public static function setCallTimes($callTimes)
    {
        self::$callTimes = $callTimes;
    }
}
