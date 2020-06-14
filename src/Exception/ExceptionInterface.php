<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Exception;

use Psr\Container\ContainerExceptionInterface;

/**
 * Base exception for all Laminas\ServiceManager exceptions.
 */
interface ExceptionInterface extends ContainerExceptionInterface, \Throwable
{
}
