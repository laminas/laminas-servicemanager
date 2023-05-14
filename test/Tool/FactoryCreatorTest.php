<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Tool\ConstructorParameterResolver\ConstructorParameterResolver;
use Laminas\ServiceManager\Tool\FactoryCreator;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest\TargetObjectDelegator;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SecondComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

use function array_pop;
use function count;
use function file_get_contents;
use function func_get_args;
use function is_array;
use function preg_match;

use const PHP_EOL;

/**
 * @covers \Laminas\ServiceManager\Tool\FactoryCreator
 */
final class FactoryCreatorTest extends TestCase
{
    private FactoryCreator $factoryCreator;

    /** @var MockObject&ContainerInterface */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container      = $this->createMock(ContainerInterface::class);
        $this->factoryCreator = new FactoryCreator(
            $this->container,
            new ConstructorParameterResolver(),
        );
    }

    public function testCreateFactoryCreatesForInvokable(): void
    {
        $className = InvokableObject::class;
        $factory   = file_get_contents(__DIR__ . '/../TestAsset/factories/InvokableObject.php');

        self::assertSame($factory, $this->factoryCreator->createFactory($className));
    }

    public function testCreateFactoryCreatesForSimpleDependencies(): void
    {
        $className = SimpleDependencyObject::class;
        $factory   = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');
        $this->container
            ->expects(self::atLeastOnce())
            ->method('has')
            ->willReturnCallback($this->createReturnMapCallbackWithDefault([
                [InvokableObject::class, true],
            ], false));

        self::assertSame($factory, $this->factoryCreator->createFactory($className));
    }

    public function testCreateFactoryCreatesForComplexDependencies(): void
    {
        $className = ComplexDependencyObject::class;
        $factory   = file_get_contents(__DIR__ . '/../TestAsset/factories/ComplexDependencyObject.php');

        $this->container
            ->expects(self::atLeastOnce())
            ->method('has')
            ->willReturnCallback($this->createReturnMapCallbackWithDefault([
                [SimpleDependencyObject::class, true],
                [SecondComplexDependencyObject::class, true],
            ], false));

        self::assertSame($factory, $this->factoryCreator->createFactory($className));
    }

    public function testNamespaceGeneration(): void
    {
        $testClassNames = [
            ComplexDependencyObject::class => 'LaminasTest\\ServiceManager\\TestAsset',
            TargetObjectDelegator::class   => 'LaminasTest\\ServiceManager\\TestAsset\\DelegatorAndAliasBehaviorTest',
            stdClass::class                => '',
        ];

        $this->container
            ->expects(self::atLeastOnce())
            ->method('has')
            ->willReturnCallback($this->createReturnMapCallbackWithDefault([
                [SimpleDependencyObject::class, true],
                [SecondComplexDependencyObject::class, true],
            ], false));

        foreach ($testClassNames as $testFqcn => $expectedNamespace) {
            $generatedFactory = $this->factoryCreator->createFactory($testFqcn);

            if ($expectedNamespace === '') {
                self::assertStringNotContainsString(PHP_EOL . 'namespace ', $generatedFactory);
                continue;
            }

            preg_match('/^namespace\s([^;]+)/m', $generatedFactory, $namespaceMatch);

            self::assertNotEmpty($namespaceMatch);
            self::assertSame($expectedNamespace, $namespaceMatch[1]);
        }
    }

    private function createReturnMapCallbackWithDefault(array $values, mixed $default): callable
    {
        return function () use ($values, $default): mixed {
            $args           = func_get_args();
            $parameterCount = count($args);

            foreach ($values as $map) {
                if (! is_array($map) || $parameterCount !== count($map) - 1) {
                    continue;
                }

                /** @var mixed $return */
                $return = array_pop($map);

                if ($args === $map) {
                    return $return;
                }
            }

            return $default;
        };
    }
}
