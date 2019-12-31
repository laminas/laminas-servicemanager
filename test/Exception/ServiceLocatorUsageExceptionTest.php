<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Exception;

use Exception;
use Laminas\ServiceManager\Exception\ServiceLocatorUsageException;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \Laminas\ServiceManager\Exception\ServiceLocatorUsageException}
 *
 * @covers \Laminas\ServiceManager\Exception\ServiceLocatorUsageException
 */
class ServiceLocatorUsageExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFromInvalidPluginManagerRequestedServiceName()
    {
        /* @var $pluginManager \Laminas\ServiceManager\AbstractPluginManager */
        $pluginManager     = $this->getMockForAbstractClass('Laminas\ServiceManager\AbstractPluginManager');
        /* @var $serviceLocator \Laminas\ServiceManager\ServiceLocatorInterface */
        $serviceLocator    = $this->getMockForAbstractClass('Laminas\ServiceManager\ServiceLocatorInterface');
        $previousException = new Exception();

        $exception = ServiceLocatorUsageException::fromInvalidPluginManagerRequestedServiceName(
            $pluginManager,
            $serviceLocator,
            'the-service',
            $previousException
        );

        $this->assertInstanceOf('Laminas\ServiceManager\Exception\ServiceLocatorUsageException', $exception);
        $this->assertInstanceOf(
            'Laminas\ServiceManager\Exception\ServiceNotFoundException',
            $exception,
            'Must be a ServiceNotFoundException for BC compatibility with older try-catch logic'
        );
        $this->assertSame($previousException, $exception->getPrevious());

        $expectedMessageFormat = <<<'MESSAGE'
Service "the-service" has been requested to plugin manager of type "%a", but couldn't be retrieved.
A previous exception of type "Exception" has been raised in the process.
By the way, a service with the name "the-service" has been found in the parent service locator "%a": did you forget to use $parentLocator = $serviceLocator->getServiceLocator() in your factory code?
MESSAGE;

        $this->assertStringMatchesFormat($expectedMessageFormat, $exception->getMessage());
    }
}
