<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\Collector;

final class NullCollector implements CollectorInterface
{
    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectAutowireFactory(string $dependencyName, array $instantiationStack): void
    {
    }

    /**
     * @param array $instantiationStack
     */
    public function collectCustomFactory(array $instantiationStack): void
    {
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectError(string $dependencyName, array $instantiationStack): void
    {
    }

    public function finish(): void
    {
    }
}
