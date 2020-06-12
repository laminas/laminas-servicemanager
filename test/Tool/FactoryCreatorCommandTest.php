<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\FactoryCreatorCommand;
use Laminas\Stdlib\ConsoleHelper;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use Prophecy\Prophecy\ObjectProphecy;
use function file_get_contents;
use function sprintf;

class FactoryCreatorCommandTest extends TestCase
{
    /** @var ObjectProphecy|ConsoleHelper */
    private $helper;

    /** @var FactoryCreatorCommand */
    private $command;

    public function setUp()
    {
        $this->helper = $this->prophesize(ConsoleHelper::class);
        $this->command = new FactoryCreatorCommand(ConfigDumperCommand::class, $this->helper->reveal());
    }

    public function testEmitsHelpWhenNoArgumentsProvided()
    {
        $command = $this->command;
        $this->assertHelp();
        self::assertEquals(0, $command([]));
    }

    public function assertHelp($stream = STDOUT)
    {
        $this->helper->writeLine(
            Argument::containingString('<info>Usage:</info>'),
            true,
            $stream
        )->shouldBeCalled();
    }

    public function helpArguments()
    {
        return [
            'short' => ['-h'],
            'long' => ['--help'],
            'literal' => ['help'],
        ];
    }

    /**
     * @dataProvider helpArguments
     */
    public function testEmitsHelpWhenHelpArgumentProvidedAsFirstArgument($argument)
    {
        $command = $this->command;
        $this->assertHelp();
        self::assertEquals(0, $command([$argument]));
    }

    public function invalidArguments()
    {
        return [
            'string' => ['string'],
            'interface' => [FactoryInterface::class],
        ];
    }

    /**
     * @dataProvider invalidArguments
     */
    public function testEmitsErrorMessageIfArgumentIsNotAClass($argument)
    {
        $command = $this->command;
        $this->assertErrorRaised(sprintf('Class "%s" does not exist', $argument));
        $this->assertHelp(STDERR);
        self::assertEquals(1, $command([$argument]));
    }

    public function assertErrorRaised($message)
    {
        $this->helper->writeErrorMessage(
            Argument::containingString($message)
        )->shouldBeCalled();
    }

    public function testEmitsErrorWhenUnableToCreateFactory()
    {
        $command = $this->command;
        $this->assertErrorRaised('Unable to create factory for "' . ObjectWithScalarDependency::class . '":');
        $this->assertHelp(STDERR);
        self::assertEquals(1, $command([ObjectWithScalarDependency::class]));
    }

    public function testEmitsFactoryFileToStdoutWhenSuccessful()
    {
        $command = $this->command;
        $expected = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');

        $this->helper->write($expected, false)->shouldBeCalled();
        self::assertEquals(0, $command([SimpleDependencyObject::class]));
    }
}
