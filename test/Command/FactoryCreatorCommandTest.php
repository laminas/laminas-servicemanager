<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Command;

use Laminas\ServiceManager\Command\FactoryCreatorCommand;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\FactoryCreatorInterface;
use LaminasTest\ServiceManager\TestAsset\ObjectWithScalarDependency;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function file_get_contents;
use function sprintf;

/**
 * @covers \Laminas\ServiceManager\Command\FactoryCreatorCommand
 */
final class FactoryCreatorCommandTest extends TestCase
{
    private FactoryCreatorCommand $command;

    /** @var FactoryCreatorInterface&MockObject */
    private FactoryCreatorInterface $factoryCreator;

    /** @var MockObject&InputInterface */
    private InputInterface $input;

    /** @var MockObject&OutputInterface */
    private OutputInterface $output;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factoryCreator = $this->createMock(FactoryCreatorInterface::class);
        $this->input          = $this->createMock(InputInterface::class);
        $this->output         = $this->createMock(OutputInterface::class);
        $this->command        = new FactoryCreatorCommand($this->factoryCreator);
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
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
        $this->input
            ->method('getArgument')
            ->with('className')
            ->willReturn($argument);

        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        $this->assertErrorRaised(sprintf('Class "%s" does not exist', $argument));
        self::assertSame(1, $this->command->run($this->input, $this->output));
    }

    public function assertErrorRaised(string $message): void
    {
        $this->output
            ->expects(self::once())
            ->method('writeln')
            ->with(self::stringContains(sprintf('<error>%s', $message)));
    }

    public function testEmitsErrorWhenUnableToCreateFactory(): void
    {
        $this->assertErrorRaised('Unable to create factory for "' . ObjectWithScalarDependency::class . '":');
        $this->input
            ->method('getArgument')
            ->with('className')
            ->willReturn(ObjectWithScalarDependency::class);
        $this->factoryCreator
            ->expects(self::once())
            ->method('createFactory')
            ->with(ObjectWithScalarDependency::class)
            ->willThrowException(new InvalidArgumentException('Foo bar'));

        self::assertSame(1, $this->command->run($this->input, $this->output));
    }

    public function testEmitsFactoryFileToStdoutWhenSuccessful(): void
    {
        $expected = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');

        $this->input
            ->method('getArgument')
            ->with('className')
            ->willReturn(SimpleDependencyObject::class);

        $this->factoryCreator
            ->expects(self::once())
            ->method('createFactory')
            ->with(SimpleDependencyObject::class)
            ->willReturn($expected);

        $this->output
            ->expects(self::once())
            ->method('writeln')
            ->with($expected);

        $this->assertSame(0, $this->command->run($this->input, $this->output));
    }
}
