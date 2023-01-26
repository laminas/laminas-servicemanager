<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class FailingExceptionWithStringAsCodeFactory implements FactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        throw new ExceptionWithStringAsCodeException('There is an error');
    }
}
