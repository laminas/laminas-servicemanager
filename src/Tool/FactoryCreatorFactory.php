<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class FactoryCreatorFactory
{
    public function __invoke(ContainerInterface $container): FactoryCreatorInterface
    {
        return new FactoryCreator($container, $container->get(ConstructorParameterResolverInterface::class));
    }
}
