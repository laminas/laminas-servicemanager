<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Exception\CyclicAliasException;

final class AliasResolver
{
    /**
     * @param array $aliases
     * @return array
     */
    public function __invoke(array $aliases): array
    {
        $resolvedAliases = [];
        foreach ($aliases as $alias => $service) {
            $visited = [];
            $name = $alias;

            while (isset($aliases[$name])) {
                if (isset($visited[$name])) {
                    throw CyclicAliasException::fromAliasesMap($aliases);
                }

                $visited[$name] = true;
                $name = $aliases[$name];
            }

            $resolvedAliases[$alias] = $name;
        }

        return $resolvedAliases;
    }
}
