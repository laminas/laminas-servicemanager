<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class InvokableObject
{
    public array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
