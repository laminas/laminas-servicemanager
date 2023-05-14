<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithConstructorAcceptingAnyArgument
{
    public array $arguments;

    public function __construct(
        mixed ...$arguments
    ) {
        $this->arguments = $arguments;
    }
}
