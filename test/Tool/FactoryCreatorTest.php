<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Tool\FactoryCreator;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\Foo;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;

use function class_alias;
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
            'Foo\\Bar\\Service'          => 'Foo\\Bar',
            'Foo\\Service\\Bar\\Service' => 'Foo\\Service\\Bar',
        ];
        foreach ($testClassNames as $testFqcn => $expectedNamespace) {
            class_alias(Foo::class, $testFqcn);
            $generatedFactory = $this->factoryCreator->createFactory($testFqcn);
            preg_match('/^namespace\s([^;]+)/m', $generatedFactory, $namespaceMatch);

            self::assertNotEmpty($namespaceMatch);
            self::assertSame($expectedNamespace, $namespaceMatch[1]);
        }
    }
}
