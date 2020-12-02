<?php

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Exception;

use DomainException;

/**
 * @inheritDoc
 */
class ContainerModificationsNotAllowedException extends DomainException implements ExceptionInterface
{
    /**
     * @param string $service Name of service that already exists.
     * @return self
     */
    public static function fromExistingService($service)
    {
        return new self(sprintf(
            'The container does not allow replacing or updating a service'
            . ' with existing instances; the following service'
            . ' already exists in the container: %s',
            $service
        ));
    }
}
