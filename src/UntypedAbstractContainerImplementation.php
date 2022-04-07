<?php

declare(strict_types=1);

namespace Laminas\ServiceManager;

use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-require-extends ServiceManager
 */
abstract class UntypedAbstractContainerImplementation implements ContainerInterface
{
    /**
     * {@inheritDoc}
     *
     * @param string|class-string $id
     * @return bool
     */
    public function has($id)
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->hasService($id);
    }

    public function get($id)
    {
        assert($this instanceof ServiceManager);
        /** @psalm-suppress InaccessibleMethod We actually can access the method, but this is hacky code. */
        return $this->getService($id);
    }
}
