<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Laminas\ServiceManager\Exception\InvalidArgumentException;

/**
 * Due to limitations, it is not possible to verify if callables or factories signatures are correct.
 * This validation will assume that callables and factories do return the appropriate types and that
 * the configuration structure matches declaration.
 *
 * @psalm-import-type ServiceManagerConfiguration from ServiceManager
 */
interface ConfigValidatorInterface
{
    /**
     * @psalm-assert ServiceManagerConfiguration $config
     * @throws InvalidArgumentException If configuration does not match expectations.
     */
    public function assertIsValidConfiguration(array $config): void;

    /**
     * @psalm-assert-if-true ServiceManagerConfiguration $config
     */
    public function isValidConfiguration(array $config): bool;
}
