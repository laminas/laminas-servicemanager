<?php

namespace LaminasBench\ServiceManager;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @Warmup(2)
 */
class FetchNewServiceUsingReflectionAbstractFactoryAsFactoryBench
{
    private ServiceManager $sm;

    public function __construct()
    {
        $this->sm = new ServiceManager([
            'services'  => [
                'config' => [],
            ],
            'factories' => [
                BenchAsset\Dependency::class               => ReflectionBasedAbstractFactory::class,
                BenchAsset\ServiceWithDependency::class    => ReflectionBasedAbstractFactory::class,
                BenchAsset\ServiceDependingOnConfig::class => ReflectionBasedAbstractFactory::class,
            ],
        ]);
    }

    public function benchFetchServiceWithNoDependencies(): void
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\Dependency::class);
    }

    public function benchBuildServiceWithNoDependencies(): void
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\Dependency::class);
    }

    public function benchFetchServiceDependingOnConfig(): void
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\ServiceDependingOnConfig::class);
    }

    public function benchBuildServiceDependingOnConfig(): void
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\ServiceDependingOnConfig::class);
    }

    public function benchFetchServiceWithDependency(): void
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\ServiceWithDependency::class);
    }

    public function benchBuildServiceWithDependency(): void
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\ServiceWithDependency::class);
    }
}
