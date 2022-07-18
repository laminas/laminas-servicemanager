<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid


declare(strict_types=1);

namespace Laminas\ServiceManager;

use Composer\InstalledVersions;
use Interop\Container\Containerinterface as InteropContainerInterface;
use Interop\Container\Exception\ContainerException as InteropContainerException;
use Interop\Container\Exception\NotFoundException as InteropNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;
use function class_alias;
use function interface_exists;
use function version_compare;

if (! interface_exists(InteropContainerInterface::class, false)) {
    class_alias(ContainerInterface::class, InteropContainerInterface::class);
}
if (! interface_exists(InteropContainerException::class, false)) {
    class_alias(ContainerExceptionInterface::class, InteropContainerException::class);
}
if (! interface_exists(InteropNotFoundException::class, false)) {
    class_alias(NotFoundExceptionInterface::class, InteropNotFoundException::class);
}

$installedContainerVersion = InstalledVersions::getVersion('psr/container');

assert(
    $installedContainerVersion !== null,
    'psr/container is required by `composer.json` and therefore this method should not return `null`.'
);

if (version_compare($installedContainerVersion, '2', '<')) {
    class_alias(AbstractUntypedContainerImplementation::class, AbstractContainerImplementation::class);
} else {
    class_alias(AbstractTypedContainerImplementation::class, AbstractContainerImplementation::class);
}
