<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

class CallTimesAbstractFactory implements AbstractFactoryInterface
{
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
    public function __invoke(ContainerInterface $container, $className, array $options = null)
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
