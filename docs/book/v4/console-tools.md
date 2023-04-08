# Console Tools

Starting in 4.0.0, `laminas-servicemanager` moved the CLI tooling to `laminas-cli` and provides several commands to be executed.

## Requirements

To run the console tools with `laminas-servicemanager` v4, the [`laminas/laminas-cli`](https://docs.laminas.dev/laminas-cli/) component needs to be added to the project dependencies.

> ### Installation
>
> ```shell
> $ composer require laminas/laminas-cli
> ```
> _In case laminas-cli is only required to consume these console tools, you might consider using the `--dev` flag._

## Available Commands

- [Generate Dependencies for Config Factory](#generate-dependencies-for-config-factory)
- [Generate Factory for Class](#generate-factory-for-class)
- [Generate Ahead of Time Factories](#ahead-of-time-factories)

## Generate Dependencies for Config Factory

```bash
$ ./vendor/bin/laminas servicemanager:generate-deps-for-config-factory -h
Description:
  Reads the provided configuration file (creating it if it does not exist), and injects it with ConfigAbstractFactory dependency configuration for the provided class name, writing the changes back to the file.

Usage:
  servicemanager:generate-deps-for-config-factory [options] [--] <configFile> <class>

Arguments:
  configFile                 Path to a config file for which to generate configuration. If the file does not exist, it will be created. If it does exist, it must return an array, and the file will be updated with new configuration.
  class                      Name of the class to reflect and for which to generate dependency configuration.

Options:
  -i, --ignore-unresolved    Ignore classes with unresolved direct dependencies.
  -q, --quiet                Do not output any message
```

This utility will generate dependency configuration for the named class for use
with the [ConfigAbstractFactory](config-abstract-factory.md). When doing so, it
will read the named configuration file (creating it if it does not exist), and
merge any configuration it generates with the return values of that file,
writing the changes back to the original file.

Since 3.2.1, the tool also supports the `-i` or `--ignore-unresolved` flag.
Use these flags when you have typehints to classes that cannot be resolved.
When you omit the flag, such classes will cause the tool to fail with an
exception message. By adding the flag, you can have it continue and produce
configuration. This option is particularly useful when typehints are on
interfaces or resolve to services served by other abstract factories.

## Generate Factory for Class

```bash
$ ./vendor/bin/laminas servicemanager:generate-factory-for-class -h
Description:
  Generates to STDOUT a factory for creating the specified class; this may then be added to your application, and configured as a factory for the class.

Usage:
  servicemanager:generate-factory-for-class <className>

Arguments:
  className                  Name of the class to reflect and for which to generate a factory.

Options:
  -q, --quiet                Do not output any message
```

This utility generates a factory class for the given class, based on the
typehints in its constructor. The factory is emitted to STDOUT, and may be piped
to a file if desired:

```bash
$ ./vendor/bin/laminas servicemanager:generate-factory-for-class \
> "Application\\Model\\AlbumModel" > ./module/Application/src/Model/AlbumModelFactory.php
```

The class generated implements `Laminas\ServiceManager\Factory\FactoryInterface`,
and is generated within the same namespace as the originating class.

## Generate Ahead of Time Factories

```bash
$ vendor/bin/laminas servicemanager:generate-aot-factories -h
Description:
  Creates factories which replace the runtime overhead for `ReflectionBasedAbstractFactory`.

Usage:
  servicemanager:generate-aot-factories [<localConfigFilename>]

Arguments:
  localConfigFilename        Should be a path targeting a filename which will be created so that the config autoloading will pick it up. Using a `.local.php` suffix should verify that the file is overriding existing configuration. [default: "config/autoload/generated-factories.local.php"]

Options:
  -q, --quiet                Do not output any message
```

This utility will generate factories in the same way as [servicemanager:generate-factory-for-class](#generate-factory-for-class). The main difference is, that it will scan the whole project configuration for the usage of `ReflectionBasedAbstractFactory` within **any** ServiceManager look-a-like configuration (i.e. explicit usage within `factories`) and auto-generates factories for all of these services **plus** creates a configuration file which overrides **all** ServiceManager look-a-like configurations so that these consume the generated factories.

For more details and how to set up a project so that all factories are properly replaced, refer to the [dedicated command documentation](ahead-of-time-factories.md). 
