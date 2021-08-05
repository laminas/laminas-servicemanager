<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class InvokableObject
{
    /** @var array */
    public $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
