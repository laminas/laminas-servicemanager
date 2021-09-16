<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

class InvokableObject
{
    public array $options;

    /**
     * @param array $options
     */
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
