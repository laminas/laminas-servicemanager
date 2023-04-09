<?php
declare(strict_types=1);

namespace LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Tool\AheadOfTimeFactoryCompiler\AheadOfTimeFactoryCompiler;
use Laminas\ServiceManager\Tool\FactoryCreatorInterface;
use LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler\TestAsset\WhateverEnum;
use LaminasTest\ServiceManager\Tool\AheadOfTimeFactoryCompiler\TestAsset\WhateverTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use const PHP_VERSION_ID;

final class AheadOfTimeFactoryCompilerTest extends TestCase
{
    private AheadOfTimeFactoryCompiler $compiler;

    /**
     * @var FactoryCreatorInterface&MockObject
     */
    private FactoryCreatorInterface $factoryCreator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factoryCreator = $this->createMock(FactoryCreatorInterface::class);
        $this->compiler = new AheadOfTimeFactoryCompiler(
            $this->factoryCreator,
        );
    }

    public function testCanHandleConfigWithoutServicesRegisteredWithReflectionBasedAbstractFactory(): void
    {
        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        self::assertSame([], $this->compiler->compile([]));
    }

    public function testCanHandleLaminasMvcServiceManagerConfiguration(): void
    {
        $config = [
            'service_manager' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::once())
            ->method('createFactory')
            ->with(stdClass::class)
            ->willReturn('created factory');

        $factories = $this->compiler->compile($config);
        self::assertCount(1, $factories);
        $factory = $factories[0];
        self::assertSame('service_manager', $factory->containerConfigurationKey);
        self::assertSame(stdClass::class, $factory->fullyQualifiedClassName);
        self::assertSame('created factory', $factory->generatedFactory);
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public function nonClassReferencingServiceNames(): array
    {
        return [
            'nonexistent-service-name' => [
                'foobar',
            ],
            'interface' => [
                FactoryInterface::class,
            ],
            'trait' => [
                WhateverTrait::class,
            ],
        ];
    }

    /**
     * @return array<non-empty-string,array{string}>
     */
    public function nonClassReferencingServiceNamesPhp81Upwards(): array
    {
        if (PHP_VERSION_ID < 80100) {
            return [];
        }

        return [
            'enum' => [
                WhateverEnum::class,
            ],
        ];
    }

    /**
     * @dataProvider nonClassReferencingServiceNames
     * @dataProvider nonClassReferencingServiceNamesPhp81Upwards
     */
    public function testWillRaiseExceptionWhenFactoryIsUsedWithNonClassReferencingService(string $serviceName): void
    {
        $config = [
            'dependencies' => [
                'factories' => [
                    $serviceName => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist or does not refer to an actual class');

        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        $this->compiler->compile($config);
    }

    public function testWillDetectSameServiceProvidedByMultipleServiceOrPluginManagers(): void
    {
        $config = [
            'foo' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
            'bar' => [
                'factories' => [
                    stdClass::class => ReflectionBasedAbstractFactory::class,
                ],
            ],
        ];

        $this->factoryCreator
            ->expects(self::never())
            ->method(self::anything());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is registered in (at least) two service-/plugin-managers: foo, bar');

        $this->compiler->compile($config);
    }
}
