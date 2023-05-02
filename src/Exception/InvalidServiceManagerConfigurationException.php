<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Exception;

use function get_debug_type;
use function sprintf;

final class InvalidServiceManagerConfigurationException extends InvalidArgumentException
{
    /**
     * @internal
     *
     * @param non-empty-string $identifier
     */
    public static function fromNonArrayConfiguration(string $identifier, mixed $value): self
    {
        return new self(sprintf(
            'Configuration `%s` is expected to represent an array structure; "%s" given',
            $identifier,
            get_debug_type($value)
        ));
    }

    /**
     * @internal
     *
     * @param non-empty-string $identifier
     */
    public static function fromNonMapConfiguration(string $identifier): self
    {
        return new self(sprintf(
            'Configuration `%s` is expected to represent an array map',
            $identifier
        ));
    }
}
