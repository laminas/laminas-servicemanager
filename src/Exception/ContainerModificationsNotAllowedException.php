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

    public function __construct($service)
    {
        parent::__construct(sprintf(
            'The container does not allow to replace/update a service'
            . ' with existing instances; the following '
            . 'already exist in the container: %s',
            $service
        ));
    }
}
