<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager;

use \Laminas\ServiceManager\ServiceManager;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * @requires PHP 5.4
 * @group    Laminas_ServiceManager
 */
class ServiceLocatorAwareTraitTest extends TestCase
{
    public function testSetServiceLocator()
    {
        $object = $this->getObjectForTrait('\Laminas\ServiceManager\ServiceLocatorAwareTrait');

        $this->assertAttributeEquals(null, 'serviceLocator', $object);

        $serviceLocator = new ServiceManager;

        $object->setServiceLocator($serviceLocator);

        $this->assertAttributeEquals($serviceLocator, 'serviceLocator', $object);
    }

    public function testGetServiceLocator()
    {
        $object = $this->getObjectForTrait('\Laminas\ServiceManager\ServiceLocatorAwareTrait');

        $this->assertNull($object->getServiceLocator());

        $serviceLocator = new ServiceManager;

        $object->setServiceLocator($serviceLocator);

        $this->assertEquals($serviceLocator, $object->getServiceLocator());
    }
}
