<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;

use function array_filter;
use function array_key_exists;
use function assert;
use function class_exists;
use function is_array;
use function is_string;
use function sprintf;

use const ARRAY_FILTER_USE_BOTH;

final class AheadOfTimeFactoryCompiler implements AheadOfTimeFactoryCompilerInterface
{
    public function __construct(
        private FactoryCreatorInterface $factoryCreator,
    ) {
    }

    public function compile(iterable $config): array
    {
        $servicesRegisteredByReflectionBasedFactory = $this->extractServicesRegisteredByReflectionBasedFactory(
            $config
        );

        $compiledFactories = [];

        foreach ($servicesRegisteredByReflectionBasedFactory as $service => [$containerConfigurationKey, $aliases]) {
            $compiledFactories[] = new AheadOfTimeCompiledFactory(
                $service,
                $containerConfigurationKey,
                $this->factoryCreator->createFactory($service, $aliases),
            );
        }

        return $compiledFactories;
    }

    /**
     * @param iterable $config
     * @return array<class-string,array{non-empty-string,array<string,string>}>
     */
    private function extractServicesRegisteredByReflectionBasedFactory(iterable $config): array
    {
        $services = [];

        foreach ($config as $key => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if (! array_key_exists('factories', $entry) || ! is_array($entry['factories'])) {
                continue;
            }

            /** @var array<string,ReflectionBasedAbstractFactory|class-string<ReflectionBasedAbstractFactory>> $servicesUsingReflectionBasedFactory */
            $servicesUsingReflectionBasedFactory = array_filter(
                $entry['factories'],
                static fn(mixed $value): bool =>
                    $value === ReflectionBasedAbstractFactory::class
                    || $value instanceof ReflectionBasedAbstractFactory,
                ARRAY_FILTER_USE_BOTH,
            );

            if ($servicesUsingReflectionBasedFactory === []) {
                continue;
            }

            assert(is_string($key) && $key !== '');

            foreach ($servicesUsingReflectionBasedFactory as $service => $factory) {
                if (! class_exists($service)) {
                    throw new InvalidArgumentException(sprintf(
                        'Configured service "%s" using the `ReflectionBasedAbstractFactory` does not exist.',
                        $service
                    ));
                }

                if (isset($services[$service])) {
                    throw new InvalidArgumentException(sprintf(
                        'The exact same service "%s" is registered in (at least) two service-/plugin-managers: %s, %s',
                        $service,
                        $services[$service][0],
                        $key
                    ));
                }

                $aliases = [];
                if ($factory instanceof ReflectionBasedAbstractFactory && $factory->aliases !== []) {
                    $aliases = $factory->aliases;
                }

                $services[$service] = [$key, $aliases];
            }
        }

        return $services;
    }
}
