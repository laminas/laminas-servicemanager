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

        $serviceName = $this->config->getRealName($serviceName);
        // TODO Check if invokable has zero params
        if ($this->config->isInvokable($serviceName)) {
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
        return $this->config->getFactory($serviceName) === ReflectionBasedAbstractFactory::class;
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
            $isOptional = $parameter->isOptional() || $parameter->getType()->allowsNull();

            if ($parameter->getClass() === null) {
                // FIXME config
                if ($isOptional) {
                    return $unsatisfiedDependencies;
                }

                throw new UnexpectedScalarTypeInspectorException($serviceName, $parameter->getName());
            }

            $realDependencyName = $this->config->getRealName($parameter->getClass()->getName());
            if ($this->config->isInvokable($realDependencyName)) {
                continue;
            }

            $unsatisfiedDependencies[] = new Dependency($realDependencyName, $isOptional);
        }

        return $unsatisfiedDependencies;
    }
}
