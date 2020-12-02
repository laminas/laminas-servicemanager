<?php
/**
 * @see       https://github.com/zendframework/zend-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use Psr\Container\ContainerInterface;
use Laminas\ContainerConfigTest\AbstractMezzioContainerConfigTest;
use Laminas\ContainerConfigTest\SharedTestTrait;
use Laminas\ServiceManager\ServiceManager;

class ContainerTest extends AbstractMezzioContainerConfigTest
{
    use SharedTestTrait;

    protected function createContainer(array $config) : ContainerInterface
    {
        return new ServiceManager($config);
    }
}
