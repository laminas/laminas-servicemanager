<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidArgumentException;

use function is_callable;

/**
 * `laminas-view` HelperPluginManager is providing either an object or a callable and thus needs to provide a union
 * return type.
 *
 * @psalm-type CallableObjectType = object&callable
 * @template-extends AbstractPluginManager<callable|CallableObjectType>
 */
final class UnionPluginManager extends AbstractPluginManager
{
    /**
     * @return callable|CallableObjectType
     */
    public function getWhateverPlugin(array|null $options = null): callable|object
    {
        if ($options === null) {
            return $this->get('foo');
        }

        return $this->build('foo', $options);
    }

    public function validateWhateverPlugin(mixed $plugin): callable|object
    {
        $this->validate($plugin);
        return $plugin;
    }

    public function getConcretePlugin(): ConcreteCallablePlugin
    {
        return self::get(ConcreteCallablePlugin::class);
    }

    public function buildConcretePlugin(): ConcreteCallablePlugin
    {
        return self::build(ConcreteCallablePlugin::class);
    }

    /**
     * {@inheritDoc}
     */
    public function validate(mixed $instance): void
    {
        if (! is_callable($instance)) {
            throw new InvalidArgumentException('Provided instance is not callable.');
        }
    }
}
