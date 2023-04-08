# Ahead of Time Factories

- Since 4.0.0

In addition to the already existing [Reflection Factory](reflection-abstract-factory.md), one can create factories for those services using `ReflectionBasedAbstractFactory` before deploying the project to production.
For this purpose, a `laminas-cli` command was created. Therefore, `laminas/laminas-cli` is required as at least a `require-dev` dependency.
Using `ReflectionBasedAbstractFactory` in production is not recommended as the usage of `Reflection` is not too performant.

## Usage

It is recommended to create factories within CI pipeline. While developing a service, the `ReflectionBasedAbstractFactory` can help to dynamically extend the constructor without the need of regenerating already created/generated factories.

To generate the factories, run the following CLI command after [setting up the project](#project-setup):

```shell
$ php vendor/bin/laminas servicemanager:generate-aot-factories [<target for generated factory config>]
```

The CLI command will then scan your whole configuration for **every** container/plugin-manager look-a-like service configuration where services are using `ReflectionBasedAbstractFactory` as their factory.
Wherever `ReflectionBasedAbstractFactory` is used within a `factories` config entry, the CLI command will generate a factory while adding the replacement to the generated factory config.

When the CLI command has finished, there are all factories generated within the path (`ConfigProvider::CONFIGURATION_KEY_FACTORY_TARGET_PATH`) registered in the projects configuration along with the `<target for generated factory config>` file (defaults to `config/autoload/generated-factories.local.php`). It is required to run `composer dump-autoload` (in case you've used optimized/classmap-authoritative flag, you should pass these here again) after executing the CLI command as the autoloader has to pick up the generated factory classes. In case of an existing config cache, it is also mandatory to remove that cached configuration file.

When the project is executed having all the files in-place, the generated factory classes are picked up instead of the `ReflectionBasedAbstractFactory` and thus, no additional runtime side-effects based on `Reflection` will occur.

Ensure that both `<target for generated factory config>` file and the directory (including sub-directories and files) configured within `ConfigProvider::CONFIGURATION_KEY_FACTORY_TARGET_PATH` is being picked up when generating the artifact which is deployed to production.

## Project Setup

The project needs some additional configuration so that the generated factories are properly detected and registered.

### Additional Composer Dependencies

To execute the CLI command which auto-detects all services using the `ReflectionBasedAbstractFactory`, `laminas/laminas-cli` needs to be added as at least a dev requirement.
There is no TODO in case that `laminas/laminas-cli` is already available in the project.

```shell
$ composer require --dev laminas/laminas-cli
```

### Configuration

The configuration needs an additional configuration key which provides the target on where the generated factory classes should be stored.
One should use the `CONFIGURATION_KEY_FACTORY_TARGET_PATH` constant from `\Laminas\ServiceManager\ConfigProvider` for this.
Use either `config/autoload/global.php` (which might already exist) or the `Application`-Module configuration (`Application\Module#getConfig` or `Application\ConfigProvider#__invoke`) to do so.

Both Laminas-MVC and Mezzio do share the configuration directory structure as follows:

```text
.
├── config
│   ├── autoload
│   │   ├── global.php
│   │   └── local.php.dist
└── data
```

#### Generated Factories Location

To avoid namespace conflicts with existing modules, it is recommended to create a dedicated directory under `data` which can be used as the target directory for the generated factories.
For example: `data/GeneratedServiceManagerFactories`. This directory should contain either `.gitkeep` (in case you prefer to commit your generated factories) and/or a `.gitignore` which excludes all PHP files from being committed to your project. After adding either `.gittkeep` or `.gitignore`, head to the projects `composer.json` and add (if not yet exists) `classmap` to the `autoload` section. Within that `classmap` property, target the recently created directory where the factories are meant to be stored:

```json
{
    "name": "vendor/project",
    "type": "project",
    "[...]": {},
    "autoload": {
        "classmap": ["data/GeneratedServiceManagerFactories"]
    }
}
```

This will provide composer with the information, that PHP classes can be found within that directory and thus, all classes are automatically dumped on `composer dump-autoload` for example.

#### Configuration overrides

> ### Configuration merge strategy
>
> The `autoload` config folder is scanned for files named `[<whatever>]<environment|global|local>.php`.
> Those files containing `[*.]local.php` are ignored via `.gitignore` so that these are not accidentally committed.
> The configuration merge will happen in the following order:
>
>  1. global configurations are used first
>  2. global configurations are overridden by environment specific configurations
>  3. global and environment specific configurations are overridden by local configurations

The CLI command to generate the factories expects a path to a file, which will be created (or overridden) and which will contain **all** service <=> factory entries for the projects container and plugin-managers.

For example, if the CLI command detects `Laminas-MVC` `service_manager` service and `laminas/laminas-validator` validators using `ReflectionBasedAbstractFactory`, it will create a file like this:

```php
return [
    'service_manager' => [
        'factories' => [
            MyService::class => GeneratedMyServiceFactory::class,    
        ],
    ],
    'validators' => [
        'factories' => [
            MyValidator::class => GeneratedMyValidatorFactory::class,    
        ],
    ],
];
```

So the default location of the generated configuration which should automatically replace existing configuration (containing `ReflectionBasedAbstractFactory`) is targeted to `config/autoload/generated-factories.local.php`. Local configuration files will always replace global/environment/module configurations and therefore, it perfectly fit our needs.
