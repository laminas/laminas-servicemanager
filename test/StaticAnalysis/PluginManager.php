<?php

namespace LaminasTest\ServiceManager\StaticAnalysis;

use Laminas\ServiceManager\AbstractSingleInstancePluginManager;
use stdClass;

/**
 * @template-extends AbstractSingleInstancePluginManager<stdClass>
 */
final class PluginManager extends AbstractSingleInstancePluginManager
{
    /**
     * @var class-string<stdClass>
     */
    protected string $instanceOf = stdClass::class;

    public function getWhateverPlugin(array|null $options = null): stdClass
    {
        if ($options === null) {
            return $this->get('foo');
        }

        return $this->build('foo', $options);
    }
}
