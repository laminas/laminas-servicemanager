<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector\Collector;

use function count;
use function max;
use function printf;
use function str_repeat;

final class ConsoleCollector implements CollectorInterface
{
    private const COLOR_GREEN = "\e[1;32m";
    private const COLOR_YELLOW = "\e[33m";
    private const COLOR_RED = "\e[0;31m";
    private const COLOR_END = "\e[0m";

    private int $maxDeep = 0;
    private int $customFactoryCount = 0;
    private int $autowireFactoryCount = 0;

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectAutowireFactoryHit(string $dependencyName, array $instantiationStack): void
    {
        $this->autowireFactoryCount++;
        $this->collectDeep(count($instantiationStack));

        $prefix = $suffix = '';
        if (count($instantiationStack) === 0) {
            $prefix = self::COLOR_YELLOW;
            $suffix = self::COLOR_END;
        }
        printf(str_repeat('  ', count($instantiationStack)));
        printf("└─%s%s%s\n", $prefix, $dependencyName, $suffix);
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectCustomFactoryHit(string $dependencyName, array $instantiationStack): void
    {
        $this->collectDeep(count($instantiationStack));
        $this->customFactoryCount++;
    }

    /**
     * @param int $deep
     */
    private function collectDeep(int $deep): void
    {
        $this->maxDeep = max($deep, $this->maxDeep);
    }

    /**
     * @param string $dependencyName
     * @param array $instantiationStack
     */
    public function collectError(string $dependencyName, array $instantiationStack): void
    {
        printf(str_repeat('  ', count($instantiationStack)));
        printf("└─%s%s%s\n", self::COLOR_RED, $dependencyName, self::COLOR_END);
    }

    public function finish(): void
    {
        printf(
            "\nFactories found: %s%s%s 📦\n",
            self::COLOR_GREEN,
            $this->autowireFactoryCount + $this->customFactoryCount,
            self::COLOR_END
        );
        printf(
            "Custom factories skipped: %s%s%s 🛠️\n",
            self::COLOR_GREEN,
            $this->customFactoryCount,
            self::COLOR_END
        );
        printf(
            "Autowire factories analyzed: %s%s%s 🔥\n",
            self::COLOR_GREEN,
            $this->autowireFactoryCount,
            self::COLOR_END
        );
        printf("Maximum instantiation deep: %s%s%s 🏊\n\n", self::COLOR_GREEN, $this->maxDeep, self::COLOR_END);
        printf("As far as I can tell, %sit's all good%s 🚀\n", self::COLOR_GREEN, self::COLOR_END);
    }
}
