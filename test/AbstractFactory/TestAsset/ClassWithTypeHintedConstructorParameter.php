<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithTypeHintedConstructorParameter
{
    public function __construct(public SampleInterface $sample)
    {
    }
}
