<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use interop\container\containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SampleFactory implements FactoryInterface
{
    /**
     * @param string                   $requestedName
     * @param array<string,mixed>|null $options
     */
    public function __invoke(containerinterface $container, $requestedName, ?array $options = null): InvokableObject
    {
        return new InvokableObject();
    }
}
