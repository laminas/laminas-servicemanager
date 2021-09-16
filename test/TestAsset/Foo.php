<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class Foo
{
    protected ?array $options;

    /**
     * @param array<string,mixed>|null $options
     */
    public function __construct($options = null)
    {
        $this->options = $options;
    }
}
