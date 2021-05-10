<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Exception;

class ExceptionWithStringAsCode extends Exception
{
    /** @var string */
    protected $code = 'ExceptionString';
}
