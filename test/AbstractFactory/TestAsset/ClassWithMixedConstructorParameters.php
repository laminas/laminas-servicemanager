<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

use LaminasTest\ServiceManager\AbstractFactory\TestAsset\SampleInterface;
use LaminasTest\ServiceManager\AbstractFactory\TestAsset\ValidatorPluginManager;

class ClassWithMixedConstructorParameters
{
    public array $config;

    public ?array $options;

    public SampleInterface $sample;

    public ValidatorPluginManager $validators;

    public function __construct(
        SampleInterface $sample,
        ValidatorPluginManager $validators,
        array $config,
        ?array $options = null
    ) {
        $this->sample     = $sample;
        $this->validators = $validators;
        $this->config     = $config;
        $this->options    = $options;
    }
}
