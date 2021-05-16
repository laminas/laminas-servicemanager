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
    /**
     * @var ServiceManager
     */
    private $sm;

    public function __construct()
    {
        $this->sm = new ServiceManager([
            'services' => [
                'config' => [],
            ],
            'factories' => [
                BenchAsset\Dependency::class => ReflectionBasedAbstractFactory::class,
                BenchAsset\ServiceWithDependency::class => ReflectionBasedAbstractFactory::class,
                BenchAsset\ServiceDependingOnConfig::class => ReflectionBasedAbstractFactory::class,
            ],
        ]);
    }

    public function benchFetchServiceWithNoDependencies()
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\Dependency::class);
    }

    public function benchBuildServiceWithNoDependencies()
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\Dependency::class);
    }

    public function benchFetchServiceDependingOnConfig()
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\ServiceDependingOnConfig::class);
    }

    public function benchBuildServiceDependingOnConfig()
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\ServiceDependingOnConfig::class);
    }

    public function benchFetchServiceWithDependency()
    {
        $sm = clone $this->sm;

        $sm->get(BenchAsset\ServiceWithDependency::class);
    }

    public function benchBuildServiceWithDependency()
    {
        $sm = clone $this->sm;

        $sm->build(BenchAsset\ServiceWithDependency::class);
    }
}
