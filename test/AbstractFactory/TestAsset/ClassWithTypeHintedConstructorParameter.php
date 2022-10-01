<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithTypeHintedConstructorParameter
{
    public SampleInterface $sample;

    public function __construct(SampleInterface $sample)
    {
        $this->sample = $sample;
    }
}
