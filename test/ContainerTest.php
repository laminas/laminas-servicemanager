<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ContainerConfigTest\AbstractMezzioContainerConfigTest;
use Laminas\ContainerConfigTest\SharedTestTrait;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

/**
 * @see ConfigInterface
 *
 * @psalm-import-type ServiceManagerConfigurationType from ConfigInterface
 */
class ContainerTest extends AbstractMezzioContainerConfigTest
{
    use SharedTestTrait;

    protected function createContainer(array $config): ContainerInterface
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return new ServiceManager($config);
    }
}
