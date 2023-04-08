<?php

declare(strict_types=1);

namespace Laminas\ServiceManager\Tool;

interface AheadOfTimeFactoryCompilerInterface
{
    /**
     * @return list<AheadOfTimeCompiledFactory>
     */
    public function compile(array $config): array;
}
