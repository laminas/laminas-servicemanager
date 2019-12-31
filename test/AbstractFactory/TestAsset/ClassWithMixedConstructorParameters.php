<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

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
