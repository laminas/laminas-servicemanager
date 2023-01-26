<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class SampleFactory implements FactoryInterface
{
    /**
     * @param string                   $requestedName
     * @param array<string,mixed>|null $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): InvokableObject
    {
        return new InvokableObject();
    }
}
