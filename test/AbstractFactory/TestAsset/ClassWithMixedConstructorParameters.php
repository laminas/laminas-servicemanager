<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithMixedConstructorParameters
{
    public array $config;

    public SampleInterface $sample;

    public ValidatorPluginManager $validators;

    public ?array $options;

    public function __construct(
        array $config,
        SampleInterface $sample,
        ValidatorPluginManager $validators,
        ?array $options = null
    ) {
        $this->config     = $config;
        $this->sample     = $sample;
        $this->validators = $validators;
        $this->options    = $options;
    }
}
