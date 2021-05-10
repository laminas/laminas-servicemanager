<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithMixedConstructorParameters
{
    public $config;
    public $options;
    public $sample;
    public $validators;

    public function __construct(
        SampleInterface $sample,
        ValidatorPluginManager $validators,
        array $config,
        array $options = null
    ) {
        $this->sample = $sample;
        $this->validators = $validators;
        $this->config = $config;
        $this->options = $options;
    }
}
