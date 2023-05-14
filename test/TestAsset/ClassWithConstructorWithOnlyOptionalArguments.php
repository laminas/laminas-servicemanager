<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

final class ClassWithConstructorWithOnlyOptionalArguments
{
    public function __construct(
        array $foo = [],
        string $bar = '',
        bool $baz = true,
        int $qoo = 1,
        float $ooq = 0.0,
        ?callable $callable = null
    ) {
    }
}
