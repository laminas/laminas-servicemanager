<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Laminas\ServiceManager\Tool\ConfigDumperCommand;
use Laminas\Stdlib\ConsoleHelper;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\ObjectWithObjectScalarDependency;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function realpath;
use function sprintf;

use const STDERR;
use const STDOUT;

/**
 * @covers \Laminas\ServiceManager\Tool\ConfigDumperCommand
 */
final class ConfigDumperCommandTest extends TestCase
{
    private vfsStreamDirectory $configDir;

    /** @var ConsoleHelper&MockObject */
    private ConsoleHelper $helper;

    private ConfigDumperCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configDir = vfsStream::setup('project');
        $this->helper    = $this->createMock(ConsoleHelper::class);
        $this->command   = new ConfigDumperCommand(ConfigDumperCommand::class, $this->helper);
    }

    /**
     * @param resource $stream
     */
    public function assertHelp($stream = STDOUT): void
    {
        $this->helper
            ->expects(self::once())
            ->method('writeLine')
            ->with(
                self::stringContains('<info>Usage:</info>'),
                true,
                $stream
            );
    }

    public function assertErrorRaised(string $message): void
    {
        $this->helper
            ->expects(self::once())
            ->method('writeErrorMessage')
            ->with(self::stringContains($message));
    }

    public function testEmitsHelpWhenNoArgumentsProvided(): void
    {
        $this->assertHelp();
        self::assertEquals(0, $this->command->__invoke([]));
    }

    public static function helpArguments(): array
    {
        return [
            'short'   => ['-h'],
            'long'    => ['--help'],
            'literal' => ['help'],
        ];
    }

    public static function ignoreUnresolvedArguments(): array
    {
        return [
            'short' => ['-i'],
            'long'  => ['--ignore-unresolved'],
        ];
    }

    /**
     * @dataProvider helpArguments
     */
    public function testEmitsHelpWhenHelpArgumentProvidedAsFirstArgument(string $argument): void
    {
        $this->assertHelp();
        self::assertEquals(0, $this->command->__invoke([$argument]));
    }

    public function testEmitsErrorWhenTooFewArgumentsPresent(): void
    {
        $this->assertErrorRaised('Missing class name');
        $this->assertHelp(STDERR);
        self::assertEquals(1, $this->command->__invoke(['foo']));
    }

    public function testRaisesExceptionIfConfigFileNotFoundAndDirectoryNotWritable(): void
    {
        vfsStream::newDirectory('config', 0550)
            ->at($this->configDir);
        $config = vfsStream::url('project/config/test.config.php');

        $this->assertErrorRaised(sprintf('Cannot create configuration at path "%s"; not writable.', $config));
        $this->assertHelp(STDERR);
        self::assertEquals(1, $this->command->__invoke([$config, 'Not\A\Real\Class']));
    }

    public function testGeneratesConfigFileWhenProvidedConfigurationFileNotFound(): void
    {
        vfsStream::newDirectory('config', 0775)
            ->at($this->configDir);
        $config = vfsStream::url('project/config/test.config.php');

        $this->helper
            ->expects(self::once())
            ->method('writeLine')
            ->with('<info>[DONE]</info> Changes written to ' . $config);

        self::assertEquals(0, $this->command->__invoke([$config, SimpleDependencyObject::class]));

        $generated = include $config;

        self::assertIsArray($generated);
        self::assertArrayHasKey(ConfigAbstractFactory::class, $generated);

        $factoryConfig = $generated[ConfigAbstractFactory::class];

        self::assertIsArray($factoryConfig);
        self::assertArrayHasKey(SimpleDependencyObject::class, $factoryConfig);
        self::assertArrayHasKey(InvokableObject::class, $factoryConfig);
        self::assertContains(InvokableObject::class, $factoryConfig[SimpleDependencyObject::class]);
        self::assertEquals([], $factoryConfig[InvokableObject::class]);
    }

    /**
     * @dataProvider ignoreUnresolvedArguments
     */
    public function testGeneratesConfigFileIgnoringUnresolved(string $argument): void
    {
        vfsStream::newDirectory('config', 0775)
            ->at($this->configDir);
        $config = vfsStream::url('project/config/test.config.php');

        $this->helper
            ->expects(self::once())
            ->method('writeLine')
            ->with('<info>[DONE]</info> Changes written to ' . $config);

        self::assertEquals(0, $this->command->__invoke([$argument, $config, ObjectWithObjectScalarDependency::class]));

        $generated = include $config;

        self::assertIsArray($generated);
        self::assertArrayHasKey(ConfigAbstractFactory::class, $generated);

        $factoryConfig = $generated[ConfigAbstractFactory::class];

        self::assertIsArray($factoryConfig);
        self::assertArrayHasKey(SimpleDependencyObject::class, $factoryConfig);
        self::assertArrayHasKey(InvokableObject::class, $factoryConfig);
        self::assertContains(InvokableObject::class, $factoryConfig[SimpleDependencyObject::class]);
        self::assertEquals([], $factoryConfig[InvokableObject::class]);

        self::assertArrayHasKey(ObjectWithObjectScalarDependency::class, $factoryConfig);
        self::assertContains(
            SimpleDependencyObject::class,
            $factoryConfig[ObjectWithObjectScalarDependency::class]
        );
        self::assertContains(
            ObjectWithScalarDependency::class,
            $factoryConfig[ObjectWithObjectScalarDependency::class]
        );
    }

    public function testEmitsErrorWhenConfigurationFileDoesNotReturnArray(): void
    {
        vfsStream::newFile('config/invalid.config.php')
            ->at($this->configDir)
            ->setContent(file_get_contents(realpath(__DIR__ . '/../TestAsset/config/invalid.config.php')));
        $config = vfsStream::url('project/config/invalid.config.php');

        $this->assertErrorRaised('Configuration at path "' . $config . '" does not return an array.');
        $this->assertHelp(STDERR);
        self::assertEquals(1, $this->command->__invoke([$config, 'Not\A\Real\Class']));
    }

    public function testEmitsErrorWhenClassDoesNotExist(): void
    {
        vfsStream::newFile('config/test.config.php')
            ->at($this->configDir)
            ->setContent(file_get_contents(realpath(__DIR__ . '/../TestAsset/config/test.config.php')));
        $config = vfsStream::url('project/config/test.config.php');

        $this->assertErrorRaised('Class "Not\\A\\Real\\Class" does not exist or could not be autoloaded.');
        $this->assertHelp(STDERR);
        self::assertEquals(1, $this->command->__invoke([$config, 'Not\A\Real\Class']));
    }

    public function testEmitsErrorWhenUnableToCreateConfiguration(): void
    {
        vfsStream::newFile('config/test.config.php')
            ->at($this->configDir)
            ->setContent(file_get_contents(realpath(__DIR__ . '/../TestAsset/config/test.config.php')));
        $config = vfsStream::url('project/config/test.config.php');

        $this->assertErrorRaised('Unable to create config for "' . ObjectWithScalarDependency::class . '":');
        $this->assertHelp(STDERR);
        self::assertEquals(1, $this->command->__invoke([$config, ObjectWithScalarDependency::class]));
    }

    public function testEmitsConfigFileToStdoutWhenSuccessful(): void
    {
        vfsStream::newFile('config/test.config.php')
            ->at($this->configDir)
            ->setContent(file_get_contents(realpath(__DIR__ . '/../TestAsset/config/test.config.php')));
        $config = vfsStream::url('project/config/test.config.php');

        $this->helper
            ->expects(self::once())
            ->method('writeLine')
            ->with('<info>[DONE]</info> Changes written to ' . $config);

        self::assertEquals(0, $this->command->__invoke([$config, SimpleDependencyObject::class]));

        $generated = include $config;

        self::assertIsArray($generated);
        self::assertArrayHasKey(ConfigAbstractFactory::class, $generated);

        $factoryConfig = $generated[ConfigAbstractFactory::class];

        self::assertIsArray($factoryConfig);
        self::assertArrayHasKey(SimpleDependencyObject::class, $factoryConfig);
        self::assertArrayHasKey(InvokableObject::class, $factoryConfig);
        self::assertContains(InvokableObject::class, $factoryConfig[SimpleDependencyObject::class]);
        self::assertEquals([], $factoryConfig[InvokableObject::class]);
    }
}
