<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

class ClassWithMixedConstructorParameters
{
    /** @var array */
    public $config;

    /** @var array|null */
    public $options;

    /** @var SampleInterface */
    public $sample;

    /** @var ValidatorPluginManager */
    public $validators;

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
