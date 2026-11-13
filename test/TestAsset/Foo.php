<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class Foo
{
    /**
     * @param array<string,mixed>|null $options
     */
    public function __construct(protected ?array $options = null)
    {
    }
}
