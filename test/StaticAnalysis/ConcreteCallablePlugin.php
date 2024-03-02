<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

final class ConcreteCallablePlugin
{
    public function __invoke(): mixed
    {
        return null;
    }
}
