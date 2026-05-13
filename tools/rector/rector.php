<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPhpSets(php82: true)
    ->withPaths([
        __DIR__ . '/../../src',
        __DIR__ . '/../../test',
    ]);
