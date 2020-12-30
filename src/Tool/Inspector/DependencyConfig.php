<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Tool\Inspector\Exception\MissingFactoryInspectorException;

use function class_exists;
use function in_array;

final class DependencyConfig
{
    /**
     * @psalm-var array<string, string>
     */
    private array $factories;

    /**
     * @psalm-var list<string>
     */
    private array $invokables;

    /**
     * @psalm-var array<string, string>
     */
    private array $resolvedAliases;


    /**
     * @psalm-var array<string, string> $dependencies
     */
    public function __construct(array $dependencies)
    {
        $this->factories = $this->getValidFactories($dependencies);
        $this->invokables = $dependencies['invokables'] ?? [];
        $this->resolvedAliases = (new AliasResolver())($dependencies['aliases'] ?? []);
    }

    /**
     * @return array
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @param string $dependencyName
     * @return string
     */
    public function getRealName(string $dependencyName): string
    {
        return $this->resolvedAliases[$dependencyName] ?? $dependencyName;
    }

    /**
     * @param string $dependencyName
     * @return bool
     */
    public function isInvokable(string $dependencyName): bool
    {
        $isInvokable = in_array($dependencyName, $this->invokables, true);
        $hasInvokableFactory = $this->getFactory($dependencyName) === InvokableFactory::class;

        return $isInvokable || $hasInvokableFactory;
    }

    /**
     * @param string $dependencyName
     * @return bool
     */
    public function hasFactory(string $dependencyName): bool
    {
        $factoryClass = $this->getFactory($dependencyName);
        if ($factoryClass === null) {
            return false;
        }

        // TODO check if invokable/FactoryInterface

        return class_exists($factoryClass);
    }

    /**
     * @param string $dependencyName
     * @return string|null
     */
    public function getFactory(string $dependencyName): ?string
    {
        $realName = $this->getRealName($dependencyName);

        return $this->factories[$realName] ?? null;
    }

    /**
     * @psalm-var list<string> $dependencies
     * @psalm-return list<string>
     *
     * @param array $dependencies
     * @return array
     */
    private function getValidFactories(array $dependencies): array
    {
        // TODO implement more checks here
        $factories = $dependencies['factories'] ?? [];
        foreach ($factories as $serviceName => $factoryClass) {
            if (!$this->hasFactory($serviceName)) {
                throw new MissingFactoryInspectorException($serviceName);
            }
        }

        return $factories;
    }
}
