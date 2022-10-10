<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class InvokableObject
{
    public function __construct(public array $options = [])
    {
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
