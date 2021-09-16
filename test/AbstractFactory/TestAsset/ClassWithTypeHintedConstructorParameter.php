<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithTypeHintedConstructorParameter
{
    public \LaminasTest\ServiceManager\AbstractFactory\TestAsset\SampleInterface $sample;

    public function __construct(SampleInterface $sample)
    {
        $this->sample = $sample;
    }
}
