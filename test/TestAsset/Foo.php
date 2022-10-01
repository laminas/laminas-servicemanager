<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class Foo
{
    /** @var array<string,mixed>|null */
    protected array $options;

    /**
     * @param array<string,mixed>|null $options
     */
    public function __construct(?array $options = null)
    {
        $this->options = $options;
    }
}
