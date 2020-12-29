<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Factory\InvokableFactory;

use function class_exists;
use function in_array;

final class DependencyConfig
{
    /**
     * @var array
     */
    private array $dependencies;

    /**
     * @var array
     */
    private array $resolvedAliases;

    /**
     * @param array $dependencies
     */
    public function __construct(array $dependencies)
    {
        $this->dependencies = $dependencies;
        $this->resolvedAliases = (new AliasResolver())($dependencies['aliases'] ?? []);
    }

    /**
     * @return array
     */
    public function getFactories(): array
    {
        return $this->dependencies['factories'] ?? [];
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
        $isInvokable = in_array($dependencyName, $this->dependencies['invokables'] ?? [], true);
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

        return class_exists($factoryClass);
    }

    /**
     * @param string $dependencyName
     * @return string|null
     */
    public function getFactory(string $dependencyName): ?string
    {
        $realName = $this->getRealName($dependencyName);

        return $this->dependencies['factories'][$realName] ?? null;
    }
}
