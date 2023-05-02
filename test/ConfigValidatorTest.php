<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\ConfigValidator;
use Laminas\ServiceManager\Factory\InvokableFactory;
use LaminasTest\ServiceManager\TestAsset\SimpleAbstractFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

final class ConfigValidatorTest extends TestCase
{
    private ConfigValidator $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ConfigValidator();
    }

    public function testWillValidateValidConfiguration(): void
    {
        $config = [
            'services'           => [
                'config' => ['foo' => 'bar'],
            ],
            'factories'          => [
                stdClass::class => InvokableFactory::class,
            ],
            'delegators'         => [
                stdClass::class => [
                    static function (ContainerInterface $container, string $name, callable $callback): object {
                        $instance = $callback();
                        self::assertInstanceOf(stdClass::class, $instance);
                        $instance->foo = 'bar';

                        return $instance;
                    },
                ],
            ],
            'shared'             => [
                'config'        => true,
                stdClass::class => true,
            ],
            'aliases'            => [
                'Aliased' => stdClass::class,
            ],
            'shared_by_default'  => false,
            'abstract_factories' => [
                new SimpleAbstractFactory(),
            ],
            'initializers'       => [
                static function (ContainerInterface $container, mixed $instance): void {
                    if (! $instance instanceof stdClass) {
                        return;
                    }

                    $instance->bar = 'baz';
                },
            ],
        ];

        $this->validator->assertIsValidConfiguration($config);
        self::assertTrue($this->validator->isValidConfiguration($config));
    }
}
