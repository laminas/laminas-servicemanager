<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

use Laminas\ServiceManager\ServiceManager;

final class ServiceManagerConfiguration
{
    public function acceptsServiceManagerConfiguration(): void
    {
        new ServiceManager([
            'shared_by_default' => true,
        ]);

        new ServiceManager([
            'factories' => [
                'Foo' => fn (): bool => true,
            ],
        ]);
    }
}
