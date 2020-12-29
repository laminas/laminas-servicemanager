<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Tool\Inspector\Collector\CollectorInterface;
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
     * @var CollectorInterface
     */
    private CollectorInterface $collector;

    /**
     * @param DependencyConfig $config
     * @param DependencyDetectorInterface $dependenciesDetector
     * @param CollectorInterface $collector
     */
    public function __construct(
        DependencyConfig $config,
        DependencyDetectorInterface $dependenciesDetector,
        CollectorInterface $collector
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
        foreach ($this->config->getFactories() as $dependencyName => $_) {
            if ($this->dependenciesDetector->canDetect($dependencyName)) {
                $this->walk(new Dependency($dependencyName));
            }
        }

        $this->collector->finish();
    }

    /**
     * @param Dependency $dependency
     * @param array $instantiationStack
     * @throws Throwable
     */
    private function walk(Dependency $dependency, array $instantiationStack = []): void
    {
        $this->collect($dependency, $instantiationStack);
        $this->assertCircularDependency($dependency, $instantiationStack);

        $instantiationStack[] = $dependency->getName();

        foreach ($this->detectDependencies($dependency, $instantiationStack) as $dependency) {
            if (! $this->config->hasFactory($dependency->getName()) && ! $dependency->isOptional()) {
                $this->collector->collectError($dependency->getName(), $instantiationStack);
                throw new MissingFactoryInspectorException($dependency->getName());
            }

            $this->walk($dependency, $instantiationStack);
        }
    }

    /**
     * @param Dependency $dependency
     * @param array $instantiationStack
     */
    private function assertCircularDependency(Dependency $dependency, array $instantiationStack): void
    {
        if (in_array($dependency->getName(), $instantiationStack, true)) {
            $this->collector->collectError($dependency->getName(), $instantiationStack);
            throw new CircularDependencyInspectorException($dependency->getName(), $instantiationStack);
        }
    }

    /**
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
     * @param Dependency $dependency
     * @param array $instantiationStack
     */
    private function collect(Dependency $dependency, array $instantiationStack): void
    {
        if ($this->dependenciesDetector->canDetect($dependency->getName())) {
            $this->collector->collectAutowireFactory($dependency->getName(), $instantiationStack);
        } else {
            $this->collector->collectCustomFactory($instantiationStack);
        }
    }
}
