<?php

namespace LaminasBench\ServiceManager;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @Warmup(2)
 */
class FetchNewServiceUsingConfigAbstractFactoryAsFactoryBench
{
    /** @var ServiceManager */
    private $sm;

    public function __construct()
    {
        $this->sm = new ServiceManager([
            'services'  => [
                'config' => [
                    ConfigAbstractFactory::class => [
                        BenchAsset\Dependency::class               => [],
                        BenchAsset\ServiceWithDependency::class    => [
                            BenchAsset\Dependency::class,
                        ],
                        BenchAsset\ServiceDependingOnConfig::class => [
                            'config',
                        ],
                    ],
                ],
            ],
            'factories' => [
                BenchAsset\Dependency::class               => ConfigAbstractFactory::class,
                BenchAsset\ServiceWithDependency::class    => ConfigAbstractFactory::class,
                BenchAsset\ServiceDependingOnConfig::class => ConfigAbstractFactory::class,
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
