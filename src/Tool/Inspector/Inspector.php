<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Tool\Inspector\Collector\StatsCollectorInterface;
use Laminas\ServiceManager\Tool\Inspector\DependencyDetector\DependencyDetectorInterface;
use Laminas\ServiceManager\Tool\Inspector\Exception\CircularDependencyInspectorException;
use Laminas\ServiceManager\Tool\Inspector\Exception\MissingFactoryInspectorException;
use Throwable;

use function in_array;

final class Inspector
{
    /**
     * @var DependencyConfig
     */
    private DependencyConfig $config;

    /**
     * @var DependencyDetectorInterface
     */
    private DependencyDetectorInterface $dependenciesDetector;

    /**
     * @var StatsCollectorInterface
     */
    private StatsCollectorInterface $collector;

    /**
     * @param DependencyConfig $config
     * @param DependencyDetectorInterface $dependenciesDetector
     * @param StatsCollectorInterface $collector
     */
    public function __construct(
        DependencyConfig $config,
        DependencyDetectorInterface $dependenciesDetector,
        StatsCollectorInterface $collector
    ) {
        $this->config = $config;
        $this->dependenciesDetector = $dependenciesDetector;
        $this->collector = $collector;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(): void
    {
        foreach ($this->config->getFactories() as $serviceName => $_) {
            if ($this->dependenciesDetector->canDetect($serviceName)) {
                $this->walk(new Dependency($serviceName));
            }
        }

        $this->collector->finish();
    }

    /**
     * @psalm-var list<string> $instantiationStack
     *
     * @param Dependency $dependency
     * @param array $instantiationStack
     * @throws Throwable
     */
    private function walk(Dependency $dependency, array $instantiationStack = []): void
    {
        $this->collectStats($dependency, $instantiationStack);
        $this->assertNotCircularDependency($dependency, $instantiationStack);

        $instantiationStack[] = $dependency->getName();

        foreach ($this->detectDependencies($dependency, $instantiationStack) as $childDependency) {
            if (! $this->config->hasFactory($childDependency->getName()) && ! $childDependency->isOptional()) {
                $this->collector->collectError($childDependency->getName(), $instantiationStack);
                throw new MissingFactoryInspectorException($childDependency->getName());
            }

            $this->walk($childDependency, $instantiationStack);
        }
    }

    /**
     * @psalm-var list<string> $instantiationStack
     *
     * @param Dependency $dependency
     * @param array $instantiationStack
     */
    private function assertNotCircularDependency(Dependency $dependency, array $instantiationStack): void
    {
        if (in_array($dependency->getName(), $instantiationStack, true)) {
            $this->collector->collectError($dependency->getName(), $instantiationStack);
            throw new CircularDependencyInspectorException($dependency->getName(), $instantiationStack);
        }
    }

    /**
     * @psalm-var list<string> $instantiationStack
     * @psalm-return list<string>
     *
     * @param Dependency $dependency
     * @param array $instantiationStack
     * @return array
     * @throws Throwable
     */
    private function detectDependencies(Dependency $dependency, array $instantiationStack): array
    {
        try {
            return $this->dependenciesDetector->detect($dependency->getName());
        } catch (Throwable $e) {
            $this->collector->collectError($dependency->getName(), $instantiationStack);
            throw $e;
        }
    }

    /**
     * @psalm-return list<string>
     *
     * @param Dependency $dependency
     * @param array $instantiationStack
     */
    private function collectStats(Dependency $dependency, array $instantiationStack): void
    {
        if ($this->dependenciesDetector->canDetect($dependency->getName())) {
            $this->collector->collectAutowireFactoryHit($dependency->getName(), $instantiationStack);
        } else {
            $this->collector->collectCustomFactoryHit($dependency->getName(), $instantiationStack);
        }
    }
}
