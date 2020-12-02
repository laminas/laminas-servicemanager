<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;
use RuntimeException;

use function call_user_func;
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

        call_user_func($this->assertion, $plugin);
    }
}
