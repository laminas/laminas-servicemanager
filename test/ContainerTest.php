<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ContainerConfigTest\AbstractMezzioContainerConfigTest;
use Laminas\ContainerConfigTest\SharedTestTrait;
use Laminas\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

class ContainerTest extends AbstractMezzioContainerConfigTest
{
    use SharedTestTrait;

    protected function createContainer(array $config): ContainerInterface
    {
        return new ServiceManager($config);
    }
}
