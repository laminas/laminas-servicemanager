<?php // phpcs:disable Generic.Files.LineLength.TooLong


declare(strict_types=1);

namespace LaminasTest\ServiceManager;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\Proxy\LazyServiceFactory;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\ServiceManager\TestAsset\InvokableObject;
use PHPUnit\Framework\TestCase;
use ProxyManager\Autoloader\AutoloaderInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use stdClass;

use function array_filter;
use function closedir;
use function is_dir;
use function is_file;
use function iterator_to_array;
use function mkdir;
use function opendir;
use function readdir;
use function rmdir;
use function spl_autoload_functions;
use function spl_autoload_unregister;
use function sys_get_temp_dir;
use function unlink;

/**
 * @covers \Laminas\ServiceManager\ServiceManager
 */
final class LazyServiceIntegrationTest extends TestCase
{
    /**
     * @var string
     * @psalm-var non-empty-string
     */
    public $proxyDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->proxyDir = sys_get_temp_dir() . '/laminas-servicemanager-proxy';

        if (! is_dir($this->proxyDir)) {
            mkdir($this->proxyDir);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (! is_dir($this->proxyDir)) {
            return;
        }

        $this->removeDir($this->proxyDir);

        foreach ($this->getRegisteredProxyAutoloadFunctions() as $autoloader) {
            spl_autoload_unregister($autoloader);
        }
    }

    public function removeDir(string $directory): void
    {
        $handle = opendir($directory);
        while (false !== ($item = readdir($handle))) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory . '/' . $item;

            if (is_dir($path)) {
                $this->removeDir($path);
                continue;
            }

            if (is_file($path)) {
                unlink($path);
                continue;
            }
        }

        closedir($handle);
        rmdir($directory);
    }

    public function listProxyFiles(): RegexIterator
    {
        $rdi = new RecursiveDirectoryIterator($this->proxyDir);
        $rii = new RecursiveIteratorIterator($rdi);

        return new RegexIterator($rii, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
    }

    public function assertProxyDirEmpty(string $message = ''): void
    {
        $message = $message ?: 'Expected empty proxy directory; found files';

        // AssertEquals instead AssertEmpty because the first one prints the list of files.
        self::assertEquals([], iterator_to_array($this->listProxyFiles()), $message);
    }

    public function assertProxyFileWritten(string $message = ''): void
    {
        $message = $message ?: 'Expected ProxyManager to write at least one class file; none found';

        // AssertNotEquals instead AssertNotEmpty because the first one prints the list of files.
        self::assertNotEquals([], iterator_to_array($this->listProxyFiles()), $message);
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::createLazyServiceDelegatorFactory
     */
    public function testCanUseLazyServiceFactoryFactoryToCreateLazyServiceFactoryToActAsDelegatorToCreateLazyService(): void
    {
        $config = [
            'lazy_services' => [
                'class_map'          => [
                    InvokableObject::class => InvokableObject::class,
                ],
                'proxies_namespace'  => 'TestAssetProxy',
                'proxies_target_dir' => $this->proxyDir,
                'write_proxy_files'  => true,
            ],
            'factories'     => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'delegators'    => [
                InvokableObject::class => [LazyServiceFactory::class],
            ],
        ];

        $this->assertProxyDirEmpty();

        $container = new ServiceManager($config);
        $instance  = $container->build(InvokableObject::class, ['foo' => 'bar']);

        $this->assertProxyFileWritten();

        // Test we got a usable proxy
        self::assertInstanceOf(
            InvokableObject::class,
            $instance,
            'Service returned does not extend ' . InvokableObject::class
        );
        self::assertStringContainsString(
            'TestAssetProxy',
            $instance::class,
            'Service returned does not contain expected namespace'
        );

        // Test proxying works as expected
        $options = $instance->getOptions();
        self::assertIsArray(
            $options,
            'Expected an array of options'
        );
        self::assertEquals(['foo' => 'bar'], $options, 'Options returned do not match configuration');

        $proxyAutoloadFunctions = $this->getRegisteredProxyAutoloadFunctions();
        self::assertCount(1, $proxyAutoloadFunctions, 'Only 1 proxy autoloader should be registered');
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::createLazyServiceDelegatorFactory
     */
    public function testMissingClassMapRaisesExceptionOnAttemptToRetrieveLazyService(): void
    {
        $config = [
            'lazy_services' => [],
            'factories'     => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'delegators'    => [
                InvokableObject::class => [LazyServiceFactory::class],
            ],
        ];

        $container = new ServiceManager($config);
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('class_map');
        $container->get(InvokableObject::class);
    }

    /**
     * @covers \Laminas\ServiceManager\ServiceManager::createLazyServiceDelegatorFactory
     */
    public function testWillNotGenerateProxyClassFilesByDefault(): void
    {
        $config = [
            'lazy_services' => [
                'class_map'         => [
                    InvokableObject::class => InvokableObject::class,
                ],
                'proxies_namespace' => 'TestAssetProxy',
            ],
            'factories'     => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'delegators'    => [
                InvokableObject::class => [LazyServiceFactory::class],
            ],
        ];

        $this->assertProxyDirEmpty();

        $container = new ServiceManager($config);
        $instance  = $container->build(InvokableObject::class, ['foo' => 'bar']);

        // This is the important test
        $this->assertProxyDirEmpty('Expected proxy directory to remain empty when write_proxy_files disabled');

        // Test we got a usable proxy
        self::assertInstanceOf(
            InvokableObject::class,
            $instance,
            'Service returned does not extend ' . InvokableObject::class
        );
        self::assertStringContainsString(
            'TestAssetProxy',
            $instance::class,
            'Service returned does not contain expected namespace'
        );

        // Test proxying works as expected
        $options = $instance->getOptions();
        self::assertIsArray(
            $options,
            'Expected an array of options'
        );
        self::assertEquals(['foo' => 'bar'], $options, 'Options returned do not match configuration');

        $proxyAutoloadFunctions = $this->getRegisteredProxyAutoloadFunctions();
        self::assertCount(1, $proxyAutoloadFunctions, 'Only 1 proxy autoloader should be registered');
    }

    public function testOnlyOneProxyAutoloaderItsRegisteredOnSubsequentCalls(): void
    {
        $config = [
            'lazy_services' => [
                'class_map'         => [
                    InvokableObject::class => InvokableObject::class,
                    stdClass::class        => stdClass::class,
                ],
                'proxies_namespace' => 'TestAssetProxy',
            ],
            'factories'     => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'delegators'    => [
                InvokableObject::class => [LazyServiceFactory::class],
                stdClass::class        => [LazyServiceFactory::class],
            ],
        ];

        $container = new ServiceManager($config);
        $instance  = $container->build(InvokableObject::class, ['foo' => 'bar']);

        self::assertInstanceOf(
            InvokableObject::class,
            $instance,
            'Service returned does not extend ' . InvokableObject::class
        );
        $instance = $container->build(stdClass::class, ['foo' => 'bar']);
        self::assertInstanceOf(
            stdClass::class,
            $instance,
            'Service returned does not extend ' . stdClass::class
        );

        $proxyAutoloadFunctions = $this->getRegisteredProxyAutoloadFunctions();
        self::assertCount(1, $proxyAutoloadFunctions, 'Only 1 proxy autoloader should be registered');
    }

    public function testRaisesServiceNotFoundExceptionIfRequestedLazyServiceIsNotInClassMap(): void
    {
        $config = [
            'lazy_services' => [
                'class_map'         => [
                    stdClass::class => stdClass::class,
                ],
                'proxies_namespace' => 'TestAssetProxy',
            ],
            'factories'     => [
                InvokableObject::class => InvokableFactory::class,
            ],
            'delegators'    => [
                InvokableObject::class => [LazyServiceFactory::class],
            ],
        ];

        $this->assertProxyDirEmpty();

        $container = new ServiceManager($config);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('not found in the provided services map');
        $container->build(InvokableObject::class, ['foo' => 'bar']);
    }

    /**
     * @return AutoloaderInterface[]
     */
    protected function getRegisteredProxyAutoloadFunctions(): array
    {
        $filter = static fn($autoload): bool => $autoload instanceof AutoloaderInterface;

        return array_filter(spl_autoload_functions(), $filter);
    }
}
