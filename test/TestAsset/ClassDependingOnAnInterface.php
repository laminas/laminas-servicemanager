<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\Factory\FactoryInterface;

class ClassDependingOnAnInterface
{
    public function __construct(FactoryInterface $factory)
    {
    }
}
