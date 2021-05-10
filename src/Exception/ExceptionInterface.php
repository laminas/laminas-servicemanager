<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Exception;

use Interop\Container\Exception\ContainerException;

/**
 * Base exception for all Laminas\ServiceManager exceptions.
 */
interface ExceptionInterface extends ContainerException
{
}
