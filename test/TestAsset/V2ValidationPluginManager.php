<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use RuntimeException;

use function is_callable;
use function sprintf;

final class V2ValidationPluginManager extends AbstractPluginManager
{
    /** @var (callable(mixed):void)|null */
    public $assertion;

    public function validatePlugin(mixed $plugin): void
    {
        if (! is_callable($this->assertion)) {
            throw new RuntimeException(sprintf(
                '%s requires a callable $assertion property; not currently set',
                self::class
            ));
        }

        ($this->assertion)($plugin);
    }
}
