<?php

namespace Laminas\ServiceManager;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @internal for use in abstract plugin manager
 */
final class PsrContainerDecorator implements ContainerInterface
{
    /** @var PsrContainerInterface */
    private $container;

    public function __construct(PsrContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @return PsrContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
