<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\FactoryCreatorCommand;
use Laminas\Stdlib\ConsoleHelper;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function file_get_contents;
use function sprintf;

use const STDERR;
use const STDOUT;

class FactoryCreatorCommandTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $helper;

    private FactoryCreatorCommand $command;

    public function setUp(): void
    {
        $this->helper  = $this->prophesize(ConsoleHelper::class);
        $this->command = new FactoryCreatorCommand(ConfigDumperCommand::class, $this->helper->reveal());
    }

    public function testEmitsHelpWhenNoArgumentsProvided(): void
    {
        $command = $this->command;
        $this->assertHelp();
        $this->assertEquals(0, $command([]));
    }

    /**
     * @param resource $stream
     */
    public function assertHelp($stream = STDOUT): void
    {
        $this->helper->writeLine(
            Argument::containingString('<info>Usage:</info>'),
            true,
            $stream
        )->shouldBeCalled();
    }

    public function helpArguments(): array
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
        $command = $this->command;
        $this->assertHelp();
        $this->assertEquals(0, $command([$argument]));
    }

    public function invalidArguments(): array
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
        $command = $this->command;
        $this->assertErrorRaised(sprintf('Class "%s" does not exist', $argument));
        $this->assertHelp(STDERR);
        $this->assertEquals(1, $command([$argument]));
    }

    public function assertErrorRaised(string $message): void
    {
        $this->helper->writeErrorMessage(
            Argument::containingString($message)
        )->shouldBeCalled();
    }

    public function testEmitsErrorWhenUnableToCreateFactory(): void
    {
        $command = $this->command;
        $this->assertErrorRaised('Unable to create factory for "' . ObjectWithScalarDependency::class . '":');
        $this->assertHelp(STDERR);
        $this->assertEquals(1, $command([ObjectWithScalarDependency::class]));
    }

    public function testEmitsFactoryFileToStdoutWhenSuccessful(): void
    {
        $command  = $this->command;
        $expected = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');

        $this->helper->write($expected, false)->shouldBeCalled();
        $this->assertEquals(0, $command([SimpleDependencyObject::class]));
    }
}
