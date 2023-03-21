<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\FactoryCreatorCommand;
use Laminas\Stdlib\ConsoleHelper;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function sprintf;

use const STDERR;
use const STDOUT;

/**
 * @covers \Laminas\ServiceManager\Tool\FactoryCreatorCommand
 */
final class FactoryCreatorCommandTest extends TestCase
{
    /** @var ConsoleHelper&MockObject */
    private ConsoleHelper $helper;

    private FactoryCreatorCommand $command;

    protected function setUp(): void
    {
        parent::setUp();

        $this->helper  = $this->createMock(ConsoleHelper::class);
        $this->command = new FactoryCreatorCommand(ConfigDumperCommand::class, $this->helper);
    }

    public function testEmitsHelpWhenNoArgumentsProvided(): void
    {
        $this->assertHelp();
        self::assertSame(0, $this->command->__invoke([]));
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

    public static function helpArguments(): array
    {
        return [
            'short'   => ['-h'],
            'long'    => ['--help'],
            'literal' => ['help'],
        ];
    }

    /**
     * @dataProvider helpArguments
     */
    public function testEmitsHelpWhenHelpArgumentProvidedAsFirstArgument(string $argument): void
    {
        $this->assertHelp();
        self::assertSame(0, $this->command->__invoke([$argument]));
    }

    public static function invalidArguments(): array
    {
        return [
            'string'    => ['string'],
            'interface' => [FactoryInterface::class],
        ];
    }

    /**
     * @dataProvider invalidArguments
     */
    public function testEmitsErrorMessageIfArgumentIsNotAClass(string $argument): void
    {
        $this->assertErrorRaised(sprintf('Class "%s" does not exist', $argument));
        $this->assertHelp(STDERR);
        self::assertSame(1, $this->command->__invoke([$argument]));
    }

    public function assertErrorRaised(string $message): void
    {
        $this->helper
            ->expects(self::once())
            ->method('writeErrorMessage')
            ->with(self::stringContains($message));
    }

    public function testEmitsErrorWhenUnableToCreateFactory(): void
    {
        $this->assertErrorRaised('Unable to create factory for "' . ObjectWithScalarDependency::class . '":');
        $this->assertHelp(STDERR);
        self::assertSame(1, $this->command->__invoke([ObjectWithScalarDependency::class]));
    }

    public function testEmitsFactoryFileToStdoutWhenSuccessful(): void
    {
        $expected = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');

        $this->helper
            ->expects(self::once())
            ->method('write')
            ->with($expected, false);

        $this->assertSame(0, $this->command->__invoke([SimpleDependencyObject::class]));
    }
}
