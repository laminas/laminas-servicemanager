<?php
namespace LaminasTest\ServiceManager;

use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Backport Assertion Already removed latest PHPUnit.
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/3339
 *
 * borrowed from
 * https://github.com/sebastianbergmann/phpunit/blob/7.5.9/src/Framework/Assert.php#L1408
 */
trait BackportAssertionsTrait
{

    /**
     * Asserts that a variable and an attribute of an object have the same type
     * and value.
     *
     * @param object|string $actualClassOrObject
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \ReflectionException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        $reflectionObject = new \ReflectionProperty(get_class($actualClassOrObject), $actualAttributeName);
        $reflectionObject->setAccessible(true);
        $attribute = $reflectionObject->getValue($actualClassOrObject);

        static::assertSame(
            $expected,
            $attribute,
            $message
        );
    }

    /**
     * Asserts that a variable is equal to an attribute of an object.
     *
     * @param object|string $actualClassOrObject
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \ReflectionException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3338
     */
    public static function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        if ($actualClassOrObject instanceof \stdClass) {
            $attribute = $actualClassOrObject->$actualAttributeName;
        } else {
            $reflectionObject = new \ReflectionProperty(get_class($actualClassOrObject), $actualAttributeName);
            $reflectionObject->setAccessible(true);
            $attribute = $reflectionObject->getValue($actualClassOrObject);
        }

        static::assertEquals(
            $expected,
            $attribute,
            $message
        );
    }

    /**
     * Asserts that a variable is of a given type.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @deprecated https://github.com/sebastianbergmann/phpunit/issues/3369
     */
    public static function assertInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType($expected),
            $message
        );
    }
}
