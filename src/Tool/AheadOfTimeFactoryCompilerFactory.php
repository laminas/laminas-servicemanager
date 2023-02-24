<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

use Psr\Container\ContainerInterface;

final class AheadOfTimeFactoryCompilerFactory
{
    public function __invoke(ContainerInterface $container): AheadOfTimeFactoryCompilerInterface
    {
        return new AheadOfTimeFactoryCompiler($container->get(FactoryCreatorInterface::class));
    }
}
