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
 * @Iterations(20)
 * @Warmup(2)
 */
class HasCachedServicesBench
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
            ],
            'aliases'            => [
                'alias1'          => 'service1',
                'recursiveAlias1' => 'alias1',
                'recursiveAlias2' => 'recursiveAlias1',
            ],
            'abstract_factories' => [
                BenchAsset\AbstractFactoryFoo::class,
            ],
        ]);

        // forcing initialization of all the services
        $this->sm->get('factory1');
        $this->sm->get('invokable1');
        $this->sm->get('service1');
        $this->sm->get('alias1');
        $this->sm->get('recursiveAlias1');
        $this->sm->get('recursiveAlias2');
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

    public function benchHasAlias1(): void
    {
        $this->sm->has('alias1');
    }

    public function benchHasRecursiveAlias1(): void
    {
        $this->sm->has('recursiveAlias1');
    }

    public function benchHasRecursiveAlias2(): void
    {
        $this->sm->has('recursiveAlias2');
    }

    public function benchHasAbstractFactoryService(): void
    {
        $this->sm->has('foo');
    }

    public function benchNonExistingService(): void
    {
        $this->sm->has('non-existing');
    }
}
