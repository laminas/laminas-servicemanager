<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class FailingExceptionWithStringAsCodeFactory implements FactoryInterface
{
    /** {@inheritDoc} */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): mixed
    {
        throw new ExceptionWithStringAsCodeException('There is an error');
    }
}
