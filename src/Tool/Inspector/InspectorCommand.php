<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager\Tool\Inspector;

use Laminas\ServiceManager\Tool\Inspector\Collector\UniqueHitsStatsCollectorDecorator;
use Laminas\ServiceManager\Tool\Inspector\DependencyDetector\DependencyDetectorInterface;
use Laminas\ServiceManager\Tool\Inspector\DependencyDetector\ReflectionBasedDependencyDetector;
use Laminas\ServiceManager\Tool\Inspector\Collector\ConsoleStatsCollector;
use Laminas\Stdlib\ConsoleHelper;
use Throwable;

use function array_shift;
use function dirname;
use function file_exists;
use function in_array;
use function sprintf;

use const PHP_EOL;
use const STDERR;
use const STDOUT;

final class InspectorCommand
{
    private const COMMAND_INSPECT = 'inspect';
    private const COMMAND_ERROR = 'error';
    private const COMMAND_HELP = 'help';

    private const DEFAULT_SCRIPT_NAME = __CLASS__;

    private const HELP_TEMPLATE = <<< EOH
<info>Usage:</info>

  %s [-h|--help|help] [pathToContainer]

<info>Arguments:</info>

  <info>-h|--help|help</info>   This usage message
  <info>pathToContainer</info>  Path to the container, defaults to "config/container.php" 

Verifies the container. 
EOH;

    /**
     * @var ConsoleHelper
     */
    private ConsoleHelper $helper;

    /**
     * @var string
     */
    private string $scriptName;

    /**
     * @param string $scriptName
     * @param ConsoleHelper|null $helper
     */
    public function __construct(string $scriptName = self::DEFAULT_SCRIPT_NAME, ConsoleHelper $helper = null)
    {
        $this->scriptName = $scriptName;
        $this->helper = $helper ?: new ConsoleHelper();
    }

    /**
     * @param array $args Argument list, minus script name
     * @return int Exit status
     */
    public function __invoke(array $args)
    {
        $arguments = $this->parseArgs($args);

        switch ($arguments->command) {
            case self::COMMAND_HELP:
                $this->help();
                return 0;
            case self::COMMAND_ERROR:
                $this->helper->writeErrorMessage($arguments->message);
                $this->help(STDERR);
                return 1;
            case self::COMMAND_INSPECT:
                // fall-through
            default:
                break;
        }

        $container = require $arguments->containerPath;
        try {
            $config = $container->get('config');
            // FIXME too fragile? Replace with [Mezzio|Laminas]DependenciesConfigProvider & app-side configuration?
            $dependencies = $config['dependencies'] ?? $config['service_manager'] ?? [];
            $dependenciesConfig = new DependencyConfig($dependencies);

            $dependencyDetector = new ReflectionBasedDependencyDetector($dependenciesConfig);
            if ($container->has(DependencyDetectorInterface::class)) {
                $dependencyDetector = $container->get(DependencyDetectorInterface::class);
            }

            $collector = new UniqueHitsStatsCollectorDecorator(new ConsoleStatsCollector());

            $containerInspector = new Inspector($dependenciesConfig, $dependencyDetector, $collector);
            $containerInspector();
        } catch (Throwable $e) {
            $this->helper->writeErrorMessage(PHP_EOL . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * @param array $args
     * @return \stdClass
     */
    private function parseArgs(array $args)
    {
        $arg1 = array_shift($args);
        if (in_array($arg1, ['-h', '--help', 'help'], true)) {
            return $this->createArguments(self::COMMAND_HELP);
        }

        $appDir = dirname(dirname(dirname($this->scriptName)));
        $relativeContainerPath = $arg1 ?? 'config/container.php';
        ;
        $containerPath = $appDir . '/' . $relativeContainerPath;
        if (! file_exists($containerPath)) {
            return $this->createArguments(
                self::COMMAND_ERROR,
                null,
                sprintf(
                    'Path "%s" does not exist.',
                    $containerPath
                )
            );
        }

        return $this->createArguments(self::COMMAND_INSPECT, $containerPath);
    }

    /**
     * @param string $command
     * @param string|null $containerPath Name of class to reflect.
     * @param string|null $error Error message, if any.
     * @return \stdClass
     */
    private function createArguments(string $command, string $containerPath = null, $error = null)
    {
        return (object)[
            'command' => $command,
            'containerPath' => $containerPath,
            'message' => $error,
        ];
    }

    /**
     * @param resource $resource Defaults to STDOUT
     * @return void
     */
    private function help($resource = STDOUT)
    {
        $this->helper->writeLine(
            sprintf(
                self::HELP_TEMPLATE,
                $this->scriptName
            ),
            true,
            $resource
        );
    }
}
