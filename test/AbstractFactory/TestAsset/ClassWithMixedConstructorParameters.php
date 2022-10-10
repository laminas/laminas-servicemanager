<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\AbstractFactory\TestAsset;

final class ClassWithMixedConstructorParameters
{
    public function __construct(
        public array $config,
        public SampleInterface $sample,
        public ValidatorPluginManager $validators,
        public ?array $options = null
    ) {
    }
}
