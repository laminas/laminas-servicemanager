<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Preset\Presets\Psr4Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Core', [
        'src/AbstractPluginManager.php',
        'src/AbstractSingleInstancePluginManager.php',
        'src/PluginManagerInterface.php',
        'src/ServiceLocatorInterface.php',
        'src/ServiceManager.php',
    ])
    ->layer('Config', [
        'src/ConfigProvider.php',
        'src/Module.php',
    ])
    ->layer('Exception', 'src/Exception')
    ->layer('Initializer', 'src/Initializer')
    ->layer('Factory', 'src/Factory')
    ->layer('Proxy', 'src/Proxy')
    ->layer('AbstractFactory', 'src/AbstractFactory')
    ->layer('ConstructorParameterResolver', 'src/Tool/ConstructorParameterResolver')
    ->layer('Tool', 'src/Tool')
    ->layer('Command', 'src/Command')
    ->layer('Test', 'src/Test')
    ->ruleset([
        'Initializer'                  => [],
        'Exception'                    => ['Initializer'],
        'Factory'                      => ['Exception'],
        'Proxy'                        => ['+Factory'],
        'Core'                         => ['Initializer', '+Proxy'],
        'ConstructorParameterResolver' => ['Exception'],
        'AbstractFactory'              => ['+ConstructorParameterResolver', '+Factory'],
        'Tool'                         => ['+AbstractFactory'],
        'Command'                      => ['Config', 'Core', '+Factory', 'Tool'],
        'Config'                       => ['Command', 'ConstructorParameterResolver', 'Tool'],
        'Test'                         => ['Core', 'Exception'],
    ])
    ->skip([
        Psr4Preset::CLASSES_MUST_MATCH_COMPOSER => [
            __DIR__ . '/test/TestAsset/factories',
        ],
    ]);
