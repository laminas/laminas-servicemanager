<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\TestAsset;

use Laminas\ServiceManager\InitializerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class FooInitializer implements InitializerInterface
{
    public $sm;

    protected $var;

    public function __construct($var = null)
    {
        if ($var) {
            $this->var = $var;
        }
    }

    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        $this->sm = $serviceLocator;
        if ($this->var) {
            list($key, $value) = each($this->var);
            $instance->{$key} = $value;
        }
    }
}
