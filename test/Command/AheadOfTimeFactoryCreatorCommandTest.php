<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Command;

use Laminas\ServiceManager\Command\AheadOfTimeFactoryCreatorCommand;
use Laminas\ServiceManager\ConfigProvider;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompiler\AheadOfTimeCompiledFactory;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompiler\AheadOfTimeFactoryCompilerInterface;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function assert;
use function file_get_contents;
use function sprintf;

/**
 * @covers \Laminas\ServiceManager\Command\AheadOfTimeFactoryCreatorCommand
 */
final class AheadOfTimeFactoryCreatorCommandTest extends TestCase
{
    /** @var MockObject&InputInterface */
    private InputInterface $input;

    /** @var MockObject&OutputInterface */
    private OutputInterface $output;

    private vfsStreamDirectory $factoryTargetPath;

    /** @var AheadOfTimeFactoryCompilerInterface&MockObject */
    private AheadOfTimeFactoryCompilerInterface $factoryCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->input             = $this->createMock(InputInterface::class);
        $this->output            = $this->createMock(OutputInterface::class);
        $this->factoryTargetPath = vfsStream::setup('root', 0644);
        $this->factoryCompiler   = $this->createMock(AheadOfTimeFactoryCompilerInterface::class);
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public function invalidFactoryTargetPaths(): array
    {
        $readOnlyDirectory = vfsStream::setup('read-only', 0544, ['bar' => []]);
        return [
            'no target path'        => [''],
            'read-only directory'   => [$readOnlyDirectory->getChild('bar')->url()],
            'nonexistent-directory' => ['/foo/bar/baz'],
        ];
    }

    /**
     * @dataProvider invalidFactoryTargetPaths
     */
    public function testEmitsErrorMessageIfFactoryTargetPathDoesNotMatchRequirements(string $factoryTargetPath): void
    {
        $command = new AheadOfTimeFactoryCreatorCommand([], $factoryTargetPath, $this->factoryCompiler);

        $this->factoryCompiler
            ->expects(self::never())
            ->method(self::anything());

        $this->assertErrorRaised(sprintf(
            'Please configure the `%s` configuration key in your projects config and ensure that the'
            . ' directory is registered to the composer autoloader using `classmap` and writable by the executing'
            . ' user. In case you are targeting a nonexistent directory, please create the appropriate directory'
            . ' structure before executing this command.',
            ConfigProvider::CONFIGURATION_KEY_FACTORY_TARGET_PATH
        ));
        self::assertSame(1, $command->run($this->input, $this->output));
    }

    public function assertErrorRaised(string $message): void
    {
        $this->output
            ->expects(self::once())
            ->method('writeln')
            ->with(self::stringContains(sprintf('<error>%s', $message)));
    }

    public function testWillNotCreateConfigurationFileWhenNoFactoriesDetected(): void
    {
        $directory = $this->factoryTargetPath->url();

        $command = new AheadOfTimeFactoryCreatorCommand(
            [],
            $directory,
            $this->factoryCompiler,
        );

        $this->input
            ->method('getArgument')
            ->with('localConfigFilename')
            ->willReturn(sprintf('%s/generated-factories.local.php', $directory));

        $this->output
            ->expects(self::once())
            ->method('writeln')
            ->with(
                '<comment>There is no (more) service registered to use the `ReflectionBasedAbstractFactory`.</comment>'
            );

        $this->factoryCompiler
            ->expects(self::once())
            ->method('compile')
            ->willReturn([]);

        $command->run($this->input, $this->output);

        self::assertCount(0, $this->factoryTargetPath->getChildren());
    }

    /**
     * @requires testWillVerifyLocalConfigFilenameIsWritable
     */
    public function testWillCreateExpectedGeneratedFactoriesConfig(): void
    {
        $directory = $this->factoryTargetPath->url();

        $command = new AheadOfTimeFactoryCreatorCommand(
            [],
            $directory,
            $this->factoryCompiler,
        );

        $localConfigFilename = 'yada-yada.local.php';

        $this->input
            ->method('getArgument')
            ->with('localConfigFilename')
            ->willReturn(sprintf('%s/%s', $directory, $localConfigFilename));

        $generatedFactory = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');
        assert($generatedFactory !== '');

        $this->factoryCompiler
            ->expects(self::once())
            ->method('compile')
            ->willReturn([
                new AheadOfTimeCompiledFactory(
                    SimpleDependencyObject::class,
                    'foobar',
                    $generatedFactory,
                ),
            ]);

        $this->output
            ->expects(self::once())
            ->method('writeln')
            ->with('<info>Successfully created 1 factories.</info>');

        $command->run($this->input, $this->output);

        self::assertCount(2, $this->factoryTargetPath->getChildren());
        self::assertTrue($this->factoryTargetPath->hasChild('foobar'));
        $foobarDirectory = $this->factoryTargetPath->getChild('foobar');
        self::assertInstanceOf(vfsStreamDirectory::class, $foobarDirectory);
        self::assertTrue($foobarDirectory->hasChild(
            'LaminasTest_ServiceManager_TestAsset_SimpleDependencyObjectFactory.php'
        ));
        $generatedFactoryFile = $foobarDirectory->getChild(
            'LaminasTest_ServiceManager_TestAsset_SimpleDependencyObjectFactory.php'
        );
        self::assertInstanceOf(vfsStreamFile::class, $generatedFactoryFile);
        self::assertSame($generatedFactory, $generatedFactoryFile->getContent());
        self::assertTrue($this->factoryTargetPath->hasChild('yada-yada.local.php'));
        $localConfigFile = $this->factoryTargetPath->getChild('yada-yada.local.php');
        self::assertInstanceOf(vfsStreamFile::class, $localConfigFile);
        /** @psalm-suppress UnresolvableInclude Psalm is unable to determine i/o when using vfs stream wrapper */
        $localConfiguration = require $localConfigFile->url();
        self::assertIsArray($localConfiguration, 'Expected generated local config file to return an array.');
        self::assertArrayHasKey(
            'foobar',
            $localConfiguration,
            'Expected local configuration containing an array key `foobar`'
        );
        $localFoobarServiceManagerConfiguration = $localConfiguration['foobar'];
        self::assertIsArray(
            $localFoobarServiceManagerConfiguration,
            'Expected local configuration `foobar` key provides an array structure'
        );
        self::assertArrayHasKey(
            'factories',
            $localFoobarServiceManagerConfiguration,
            'Expected local configuration `foobar` key provides an array structure with a `factories` key.'
        );
        $localFoobarServiceManagerFactories = $localFoobarServiceManagerConfiguration['factories'];
        self::assertIsArray(
            $localFoobarServiceManagerFactories,
            'Expected local configuration `foobar` key provides a factory map.'
        );
        self::assertArrayHasKey(
            SimpleDependencyObject::class,
            $localFoobarServiceManagerFactories,
            sprintf(
                'Expected local configuration `foobar` factory map provides a factory for "%s".',
                SimpleDependencyObject::class,
            ),
        );

        self::assertSame(
            sprintf('%sFactory', SimpleDependencyObject::class),
            $localFoobarServiceManagerFactories[SimpleDependencyObject::class],
        );
    }

    public function testWillVerifyLocalConfigFilenameIsWritable(): void
    {
        $localConfigFilename = sprintf('foo/bar/baz/qoo/ooq/%s', 'yada-yada.local.php');

        $directory = $this->factoryTargetPath->url();

        $command = new AheadOfTimeFactoryCreatorCommand(
            [],
            $directory,
            $this->factoryCompiler,
        );

        $localConfigPath = sprintf('%s/%s', $directory, $localConfigFilename);

        $this->input
            ->method('getArgument')
            ->with('localConfigFilename')
            ->willReturn($localConfigPath);

        $this->factoryCompiler
            ->expects(self::never())
            ->method(self::anything());

        $this->assertErrorRaised(sprintf(
            'Provided `localConfigFilename` argument "%s" is not writable. In case you are targeting a'
            . ' nonexistent directory, please create the appropriate directory structure before executing this'
            . ' command.',
            $localConfigPath,
        ));

        $command->run($this->input, $this->output);
    }
}
