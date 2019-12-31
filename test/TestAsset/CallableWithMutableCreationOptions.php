<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\MutableCreationOptionsInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use stdClass;

/**
 * implements multiple interface invokable object mock
 */
class CallableWithMutableCreationOptions implements MutableCreationOptionsInterface
{
    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }

    public function __invoke(ServiceLocatorInterface $serviceLocator, $cName, $rName)
    {
        return new stdClass;
    }
}
