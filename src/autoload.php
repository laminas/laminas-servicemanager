<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid


declare(strict_types=1);

use Interop\Container\Containerinterface as InteropContainerInterface;
use Interop\Container\Exception\ContainerException as InteropContainerException;
use Interop\Container\Exception\NotFoundException as InteropNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class_alias(ContainerInterface::class, InteropContainerInterface::class);
class_alias(ContainerExceptionInterface::class, InteropContainerException::class);
class_alias(NotFoundExceptionInterface::class, InteropNotFoundException::class);
