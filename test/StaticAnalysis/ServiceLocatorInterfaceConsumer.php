<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\StaticAnalysis;

use DateTimeImmutable;
use Laminas\ServiceManager\ServiceLocatorInterface;

use function assert;

final class ServiceLocatorInterfaceConsumer
{
    public function canInferTypeFromGet(): void
    {
        $serviceProvider = $this->getServiceProvider();

        $date = $serviceProvider->get(DateTimeImmutable::class);
        echo $date->format('Y-m-d H:i:s');

        $value = $serviceProvider->get('foo');
        assert($value === 'bar');
    }

    public function canInferTypeFromBuild(): void
    {
        $serviceProvider = $this->getServiceProvider();

        $date = $serviceProvider->build(DateTimeImmutable::class);
        echo $date->format('Y-m-d H:i:s');

        $value = $serviceProvider->build('foo');
        assert($value === 'bar');
    }

    private function getServiceProvider(): ServiceLocatorInterface
    {
        $services = [
            'foo'                    => 'bar',
            DateTimeImmutable::class => new DateTimeImmutable(),
        ];
        return new class ($services) implements ServiceLocatorInterface {
            public function __construct(private readonly array $services)
            {
            }

            public function has(string $id): bool
            {
                return isset($this->services[$id]);
            }

            public function build(string $name, ?array $options = null): mixed
            {
                /** @psalm-suppress MixedReturnStatement Yes indeed, can return mixed. */
                return $this->services[$name] ?? null;
            }

            public function get(string $id): mixed
            {
                /** @psalm-suppress MixedReturnStatement Yes indeed, can return mixed. */
                return $this->services[$id] ?? null;
            }
        };
    }
}
