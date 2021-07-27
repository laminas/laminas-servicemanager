<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid


declare(strict_types=1);

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Interop\Container\Exception\ContainerException as InteropContainerException;
use Interop\Container\Exception\NotFoundException as InteropNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

if (! class_exists(InteropContainerInterface::class)) {
    class_alias(ContainerInterface::class, InteropContainerInterface::class);
}

if (! class_exists(InteropContainerException::class)) {
    class_alias(ContainerExceptionInterface::class, InteropContainerException::class);
}

if (! class_exists(InteropNotFoundException::class)) {
    class_alias(NotFoundExceptionInterface::class, InteropNotFoundException::class);
}
