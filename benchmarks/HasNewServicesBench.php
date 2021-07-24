<?php

declare(strict_types=1);

namespace LaminasBench\ServiceManager;

use Laminas\ServiceManager\ServiceManager;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use stdClass;

/**
 * @Revs(1000)
 * @Iterations(10)
 * @Warmup(2)
 */
class HasNewServicesBench
{
    /** @var ServiceManager */
    private $sm;

    public function __construct()
    {
        $this->sm = new ServiceManager([
            'factories'          => [
                'factory1' => BenchAsset\FactoryFoo::class,
            ],
            'invokables'         => [
                'invokable1' => BenchAsset\Foo::class,
            ],
            'services'           => [
                'service1' => new stdClass(),
                'config'   => [],
            ],
            'aliases'            => [
                'factoryAlias1'          => 'factory1',
                'recursiveFactoryAlias1' => 'factoryAlias1',
                'recursiveFactoryAlias2' => 'recursiveFactoryAlias1',
            ],
            'abstract_factories' => [
                BenchAsset\AbstractFactoryFoo::class,
            ],
        ]);
    }

    public function benchHasFactory1(): void
    {
        $this->sm->has('factory1');
    }

    public function benchHasInvokable1(): void
    {
        $this->sm->has('invokable1');
    }

    public function benchHasService1(): void
    {
        $this->sm->has('service1');
    }

    public function benchFetchFactoryAlias1(): void
    {
        $this->sm->has('factoryAlias1');
    }

    public function benchHasRecursiveFactoryAlias1(): void
    {
        $this->sm->has('recursiveFactoryAlias1');
    }

    public function benchFetchRecursiveFactoryAlias2(): void
    {
        $this->sm->has('recursiveFactoryAlias2');
    }

    public function benchFetchAbstractFactoryFoo(): void
    {
        $this->sm->has('foo');
    }

    public function benchNonExistingService(): void
    {
        $this->sm->has('non-existing');
    }
}
