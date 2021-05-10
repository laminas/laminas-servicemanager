<?php

namespace LaminasBench\ServiceManager\BenchAsset;

class Foo
{
    protected $options;

    public function __construct($options = null)
    {
        $this->options = $options;
    }
}
