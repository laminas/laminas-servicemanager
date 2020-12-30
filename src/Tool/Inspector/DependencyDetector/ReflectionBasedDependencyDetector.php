<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\DependencyDetector;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Tool\Inspector\DependencyConfig;
use Laminas\ServiceManager\Tool\Inspector\Dependency;
use Laminas\ServiceManager\Tool\Inspector\Exception\UnexpectedScalarTypeInspectorException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

final class ReflectionBasedDependencyDetector implements DependencyDetectorInterface
{
    /**
     * @var DependencyConfig
     */
    private DependencyConfig $config;

    /**
     * @param DependencyConfig $config
     */
    public function __construct(DependencyConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $serviceName
     * @return array
     * @throws ReflectionException
     */
    public function detect(string $serviceName): array
    {
        if (! $this->canDetect($serviceName)) {
            return [];
        }

        // TODO throw an exception on interface

        $realServiceName = $this->config->getRealName($serviceName);
        // TODO Check if invokable has zero params
        if ($this->config->isInvokable($realServiceName)) {
            return [];
        }

        return $this->getConstructorParameters($serviceName);
    }


    /**
     * @param string $serviceName
     * @return bool
     */
    public function canDetect(string $serviceName): bool
    {
        $class = $this->config->getFactory($serviceName);

        return $class === ReflectionBasedAbstractFactory::class;
    }

    /**
     * @param string $serviceName
     * @return array
     * @throws ReflectionException
     */
    private function getConstructorParameters(string $serviceName): array
    {
        $reflectionClass = new ReflectionClass($serviceName);
        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $unsatisfiedDependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $this->assertHasClassTypeHint($parameter, $serviceName);
            $realDependencyName = $this->config->getRealName($parameter->getClass()->getName());
            if ($this->config->isInvokable($realDependencyName)) {
                continue;
            }

            $unsatisfiedDependencies[] = new Dependency($realDependencyName, $this->isOptional($parameter));
        }

        return $unsatisfiedDependencies;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param string $serviceName
     */
    private function assertHasClassTypeHint(ReflectionParameter $parameter, string $serviceName): void
    {
        if ($parameter->getClass() === null) {
            // FIXME config param
            if (! $this->isOptional($parameter)) {
                throw new UnexpectedScalarTypeInspectorException($serviceName, $parameter->getName());
            }
        }
    }

    /**
     * @param ReflectionParameter $parameter
     * @return bool
     */
    private function isOptional(ReflectionParameter $parameter): bool
    {
        return $parameter->isOptional() || ($parameter->hasType() && $parameter->getType()->allowsNull());
    }
}
