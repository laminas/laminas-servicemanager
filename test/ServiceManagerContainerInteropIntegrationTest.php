<?php // phpcs:disable WebimpressCodingStandard.PHP.CorrectClassNameCase.Invalid


declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ServiceManagerContainerInteropIntegrationTest extends TestCase
{
    private ServiceManager $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ServiceManager([]);
    }

    /**
     * NOTE: using try-catch here is to avoid phpunits exception comparison.
     *       PHPUnit is not directly catching specific exceptions when using {@see TestCase::expectException()}
     *       but catches {@see Throwable} and does an "instanceof" comparison.
     */
    public function testUpstreamCanCatchNotFoundException(): void
    {
        try {
            $this->container->get('unexisting service');
            self::fail('No exception was thrown.');
        } catch (NotFoundException $exception) {
            self::assertStringContainsString(
                'Unable to resolve service "unexisting service" to a factory',
                $exception->getMessage()
            );
        }
    }

    /**
     * NOTE: using try-catch here is to avoid phpunits exception comparison.
     *       PHPUnit is not directly catching specific exceptions when using {@see TestCase::expectException()}
     *       but catches {@see Throwable} and does an "instanceof" comparison.
     */
    public function testUpstreamCanCatchContainerException(): void
    {
        try {
            $this->container->get('unexisting service');
            self::fail('No exception was thrown.');
        } catch (ContainerException $exception) {
            self::assertStringContainsString(
                'Unable to resolve service "unexisting service" to a factory',
                $exception->getMessage()
            );
        }
    }

    public function testUpstreamCanUseInteropContainerForMethodSignature(): void
    {
        $factory = new class implements FactoryInterface {
            /** @param string $requestedName */
            public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): stdClass
            {
                return new stdClass();
            }
        };

        $instance = $factory($this->container, stdClass::class);

        self::assertInstanceOf(stdClass::class, $instance);
    }
}
