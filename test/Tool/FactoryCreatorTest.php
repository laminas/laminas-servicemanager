<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool;

use Laminas\ServiceManager\Tool\FactoryCreator;
use LaminasTest\ServiceManager\TestAsset\ComplexDependencyObject;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use LaminasTest\ServiceManager\TestAsset\SimpleDependencyObject;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

class FactoryCreatorTest extends TestCase
{
    /**
     * @var FactoryCreator
     */
    private $factoryCreator;

    /**
     * @internal param FactoryCreator $factoryCreator
     */
    public function setUp(): void
    {
        $this->factoryCreator = new FactoryCreator();
    }

    public function testCreateFactoryCreatesForInvokable()
    {
        $className = InvokableObject::class;
        $factory = file_get_contents(__DIR__ . '/../TestAsset/factories/InvokableObject.php');

        $this->assertEquals($factory, $this->factoryCreator->createFactory($className));
    }

    public function testCreateFactoryCreatesForSimpleDependencies()
    {
        $className = SimpleDependencyObject::class;
        $factory = file_get_contents(__DIR__ . '/../TestAsset/factories/SimpleDependencyObject.php');

        $this->assertEquals($factory, $this->factoryCreator->createFactory($className));
    }

    public function testCreateFactoryCreatesForComplexDependencies()
    {
        $className = ComplexDependencyObject::class;
        $factory = file_get_contents(__DIR__ . '/../TestAsset/factories/ComplexDependencyObject.php');

        $this->assertEquals($factory, $this->factoryCreator->createFactory($className));
    }
}
