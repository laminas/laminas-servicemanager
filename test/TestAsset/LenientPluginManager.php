<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<mixed>
 */
final class LenientPluginManager extends AbstractPluginManager
{
}
