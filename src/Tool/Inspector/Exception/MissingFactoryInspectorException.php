<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\Exception;

use LogicException;
use Throwable;

use function sprintf;

final class MissingFactoryInspectorException extends LogicException implements InspectorExceptionInterface
{
    /**
     * @param string $name
     * @param Throwable|null $previous
     */
    public function __construct(string $name, Throwable $previous = null)
    {
        parent::__construct(sprintf("No factory is provided for '%s' service.", $name), 0, $previous);
    }
}
