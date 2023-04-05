<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

interface FactoryCreatorInterface
{
    /**
     * @param class-string $className
     * @return non-empty-string
     */
    public function createFactory(string $className): string;
}
