<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class ClassWithCallbackMethod
{
    public function __construct(
        private readonly string $callbackValue,
    ) {
    }

    public function callback(): string
    {
        return $this->callbackValue;
    }
}
