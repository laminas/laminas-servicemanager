<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use RuntimeException;

use function is_callable;
use function sprintf;

class V2ValidationPluginManager extends AbstractPluginManager
{
    public $assertion;

    public function validatePlugin($plugin)
    {
        if (! is_callable($this->assertion)) {
            throw new RuntimeException(sprintf(
                '%s requires a callable $assertion property; not currently set',
                __CLASS__
            ));
        }

        ($this->assertion)($plugin);
    }
}
