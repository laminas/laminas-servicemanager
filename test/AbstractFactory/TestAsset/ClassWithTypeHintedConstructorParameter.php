<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithTypeHintedConstructorParameter
{
    /** @var SampleInterface */
    public $sample;

    public function __construct(SampleInterface $sample)
    {
        $this->sample = $sample;
    }
}
