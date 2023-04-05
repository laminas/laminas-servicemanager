<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Tool\FactoryCreator;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\DelegatorAndAliasBehaviorTest\TargetObjectDelegator;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function preg_match;

/**
 * @covers \Laminas\ServiceManager\Tool\FactoryCreator
 */
final class FactoryCreatorTest extends TestCase
{
    private FactoryCreator $factoryCreator;

    /**
     * @internal param FactoryCreator $factoryCreator
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->factoryCreator = new FactoryCreator();
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

        self::assertSame($factory, $this->factoryCreator->createFactory($className));
    }

    public function testCreateFactoryCreatesForComplexDependencies(): void
    {
        $className = ComplexDependencyObject::class;
        $factory   = file_get_contents(__DIR__ . '/../TestAsset/factories/ComplexDependencyObject.php');

        self::assertSame($factory, $this->factoryCreator->createFactory($className));
    }

    public function testNamespaceGeneration(): void
    {
        $testClassNames = [
            ComplexDependencyObject::class => 'LaminasTest\\ServiceManager\\TestAsset',
            TargetObjectDelegator::class   => 'LaminasTest\\ServiceManager\\TestAsset\\DelegatorAndAliasBehaviorTest',
        ];
        foreach ($testClassNames as $testFqcn => $expectedNamespace) {
            $generatedFactory = $this->factoryCreator->createFactory($testFqcn);
            preg_match('/^namespace\s([^;]+)/m', $generatedFactory, $namespaceMatch);

            self::assertNotEmpty($namespaceMatch);
            self::assertSame($expectedNamespace, $namespaceMatch[1]);
        }
    }
}
