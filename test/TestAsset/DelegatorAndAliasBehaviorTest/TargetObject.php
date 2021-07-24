<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest;

final class TargetObject
{
    public const INITIAL_VALUE = 'Default';

    /** @var string */
    public $value;

    public function __construct()
    {
        $this->value = self::INITIAL_VALUE;
    }
}
