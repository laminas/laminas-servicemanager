<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\Exception;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use LogicException;
use Throwable;

use function sprintf;

final class UnexpectedScalarTypeInspectorException extends LogicException implements InspectorExceptionInterface
{
    /**
     * @param string $serviceName
     * @param string $paramName
     * @param Throwable|null $previous
     */
    public function __construct(string $serviceName, string $paramName, Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                "%s cannot resolve scalar '%s' for '%s' service.",
                ReflectionBasedAbstractFactory::class,
                $paramName,
                $serviceName
            ),
            0,
            $previous
        );
    }
}
