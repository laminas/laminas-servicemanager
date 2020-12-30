<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\Collector;

final class UniqueHitsStatsCollectorDecorator implements StatsCollectorInterface
{
    /**
     * @var StatsCollectorInterface
     */
    private StatsCollectorInterface $collector;

    /**
     * @psalm-var list<string>
     */
    private array $hits = [];

    /**
     * @param StatsCollectorInterface $collector
     */
    public function __construct(StatsCollectorInterface $collector)
    {
        $this->collector = $collector;
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectAutowireFactoryHit(string $dependencyName, array $instantiationStack): void
    {
        if (isset($this->hits[$dependencyName])) {
            return;
        }

        $this->hits[$dependencyName] = true;

        $this->collector->collectAutowireFactoryHit($dependencyName, $instantiationStack);
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectCustomFactoryHit(string $dependencyName, array $instantiationStack): void
    {
        if (isset($this->hits[$dependencyName])) {
            return;
        }

        $this->hits[$dependencyName] = true;

        $this->collector->collectCustomFactoryHit($dependencyName, $instantiationStack);
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectError(string $dependencyName, array $instantiationStack): void
    {
        $this->collector->collectError($dependencyName, $instantiationStack);
    }

    public function finish(): void
    {
        $this->collector->finish();
    }
}

