<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<mixed>
 */
final class MixedPluginManager extends AbstractPluginManager
{
    public function getWhateverPlugin(array|null $options = null): mixed
    {
        if ($options === null) {
            return $this->get('foo');
        }

        return $this->build('foo', $options);
    }

    public function functionValidateWhateverPlugin(mixed $plugin): mixed
    {
        $this->validate($plugin);
        return $plugin;
    }

    public function validate(mixed $instance): void
    {
    }
}
