<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

if (! interface_exists(Interop\Container\ContainerInterface::class)) {
    class_alias(
        Psr\Container\ContainerInterface::class,
        Interop\Container\ContainerInterface::class
    );

    class_alias(
        Psr\Container\ContainerExceptionInterface::class,
        Interop\Container\Exception\ContainerException::class
    );

    class_alias(
        Psr\Container\NotFoundExceptionInterface::class,
        Interop\Container\Exception\NotFoundException::class
    );
}
