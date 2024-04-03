# Generate Dependencies for Config Factory

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
with the [ConfigAbstractFactory](../config-abstract-factory.md). When doing so, it
will read the named configuration file (creating it if it does not exist), and
merge any configuration it generates with the return values of that file,
writing the changes back to the original file.

The tool also supports the `-i` or `--ignore-unresolved` flag.
Use these flags when you have typehints to classes that cannot be resolved.
When you omit the flag, such classes will cause the tool to fail with an
exception message. By adding the flag, you can have it continue and produce
configuration. This option is particularly useful when typehints are on
interfaces or resolve to services served by other abstract factories.