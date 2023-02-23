<?php

declare(strict_types=1);

namespace LaminasTest\ServiceManager\Exception;

use Laminas\ServiceManager\Exception\CyclicAliasException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laminas\ServiceManager\Exception\CyclicAliasException
 */
final class CyclicAliasExceptionTest extends TestCase
{
    /**
     * @dataProvider cyclicAliasProvider
     * @param string               $alias, conflicting alias key
     * @param array<string,string> $aliases
     */
    public function testFromCyclicAlias(string $alias, array $aliases, string $expectedMessage): void
    {
        $exception = CyclicAliasException::fromCyclicAlias($alias, $aliases);

        self::assertInstanceOf(CyclicAliasException::class, $exception);
        self::assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * Test data provider for testFromCyclicAlias
     *
     * @return array<
     *     non-empty-string,
     *     array{0:non-empty-string,1:array<non-empty-string,non-empty-string>,2:non-empty-string}
     * >
     */
    public function cyclicAliasProvider(): array
    {
        return [
            'a -> a'           => [
                'a',
                [
                    'a' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "a -> a\n",
            ],
            'a -> b -> a'      => [
                'a',
                [
                    'a' => 'b',
                    'b' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "a -> b -> a\n",
            ],
            'b -> a -> b'      => [
                'b',
                [
                    'a' => 'b',
                    'b' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "b -> a -> b\n",
            ],
            'a -> b -> c -> a' => [
                'a',
                [
                    'a' => 'b',
                    'b' => 'c',
                    'c' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "a -> b -> c -> a\n",
            ],
            'b -> c -> a -> b' => [
                'b',
                [
                    'a' => 'b',
                    'b' => 'c',
                    'c' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "b -> c -> a -> b\n",
            ],
            'c -> a -> b -> c' => [
                'c',
                [
                    'a' => 'b',
                    'b' => 'c',
                    'c' => 'a',
                ],
                "A cycle was detected within the aliases definitions:\n"
                . "c -> a -> b -> c\n",
            ],
        ];
    }

    /**
     * @dataProvider aliasesProvider
     * @param string[] $aliases
     */
    public function testFromAliasesMap(array $aliases, string $expectedMessage): void
    {
        $exception = CyclicAliasException::fromAliasesMap($aliases);

        self::assertInstanceOf(CyclicAliasException::class, $exception);
        self::assertSame($expectedMessage, $exception->getMessage());
    }

    /**
     * @return array<string, array{0: array<string, string>, 1: string}>
     */
    public function aliasesProvider(): array
    {
        return [
            'empty set'                            => [
                [],
                'A cycle was detected within the following aliases map:

[

]',
            ],
            'acyclic set'                          => [
                [
                    'b' => 'a',
                    'd' => 'c',
                ],
                'A cycle was detected within the following aliases map:

[
"b" => "a"
"d" => "c"
]',
            ],
            'acyclic self-referencing set'         => [
                [
                    'b' => 'a',
                    'c' => 'b',
                    'd' => 'c',
                ],
                'A cycle was detected within the following aliases map:

[
"b" => "a"
"c" => "b"
"d" => "c"
]',
            ],
            'cyclic set'                           => [
                [
                    'b' => 'a',
                    'a' => 'b',
                ],
                'Cycles were detected within the provided aliases:

[
"b" => "a" => "b"
]

The cycle was detected in the following alias map:

[
"b" => "a"
"a" => "b"
]',
            ],
            'cyclic set (indirect)'                => [
                [
                    'b' => 'a',
                    'c' => 'b',
                    'a' => 'c',
                ],
                'Cycles were detected within the provided aliases:

[
"b" => "a" => "c" => "b"
]

The cycle was detected in the following alias map:

[
"b" => "a"
"c" => "b"
"a" => "c"
]',
            ],
            'cyclic set + acyclic set'             => [
                [
                    'b' => 'a',
                    'a' => 'b',
                    'd' => 'c',
                ],
                'Cycles were detected within the provided aliases:

[
"b" => "a" => "b"
]

The cycle was detected in the following alias map:

[
"b" => "a"
"a" => "b"
"d" => "c"
]',
            ],
            'cyclic set + reference to cyclic set' => [
                [
                    'b' => 'a',
                    'a' => 'b',
                    'c' => 'a',
                ],
                'Cycles were detected within the provided aliases:

[
"b" => "a" => "b"
"c" => "a" => "b" => "c"
]

The cycle was detected in the following alias map:

[
"b" => "a"
"a" => "b"
"c" => "a"
]',
            ],
        ];
    }
}
